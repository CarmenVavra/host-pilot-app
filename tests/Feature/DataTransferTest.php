<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ingredient;
use App\Models\Person;
use App\Models\Recipe;
use App\Models\ShoppingItem;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataTransferTest extends TestCase
{
    use RefreshDatabase;

    private string $file;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->file = sys_get_temp_dir().'/hostpilot-test-export-'.uniqid().'.json';
    }

    protected function tearDown(): void
    {
        File::delete($this->file);

        parent::tearDown();
    }

    private ?string $pngContent = null;

    private function png(): string
    {
        if ($this->pngContent === null) {
            // keep the fake file in a variable: its temp file is deleted as soon as it is garbage collected
            $image = UploadedFile::fake()->image('cover.png', 20, 20);
            $this->pngContent = (string) file_get_contents($image->getRealPath());
        }

        return $this->pngContent;
    }

    private function source(): User
    {
        $user = User::factory()->create(['email' => 'quelle@example.com', 'password' => 'geheim-passwort-123']);

        Storage::disk('public')->put('events/cover.png', $this->png());
        Storage::disk('public')->put('recipes/dish.png', $this->png());

        $event = Event::factory()->for($user)->create([
            'title' => 'Chalet Revival', 'date' => '2026-10-24', 'time' => '10:00',
            'location' => 'Bei Mira', 'guests' => 3, 'cover_style' => 5, 'cover_path' => 'events/cover.png',
        ]);

        $carmen = Person::factory()->for($event)->create(['name' => 'Carmen', 'initials' => 'CA', 'color' => 0]);
        Person::factory()->for($event)->create(['name' => 'Alle', 'initials' => 'AL', 'color' => 3]);

        $event->agendaItems()->create(['time' => '07:00', 'title' => 'Aufstehen', 'note' => 'Kaffee', 'person_id' => $carmen->id]);
        $event->agendaItems()->create(['time' => '10:00', 'title' => 'Ankunft', 'note' => null, 'person_id' => null]);

        $recipe = Recipe::factory()->for($event)->create([
            'title' => 'Shakshuka', 'course' => 'Brunch', 'portions' => 4, 'duration_minutes' => 35,
            'diet' => 'vegetarisch', 'steps' => ['Dünsten.', 'Eier stocken lassen.'], 'cover_path' => 'recipes/dish.png',
        ]);
        Ingredient::factory()->for($recipe)->create(['name' => 'Eier', 'amount' => 4, 'unit' => 'Stk', 'category' => 'Milch & Eier', 'position' => 0]);
        Ingredient::factory()->for($recipe)->create(['name' => 'Pelati', 'amount' => 800.5, 'unit' => 'g', 'category' => 'Gewürze & Vorrat', 'position' => 1]);

        ShoppingItem::factory()->for($event)->create(['name' => 'Eier', 'amount' => 4, 'unit' => 'Stk', 'category' => 'Milch & Eier', 'done' => true, 'source' => 'recipe']);
        ShoppingItem::factory()->for($event)->create(['name' => 'Kerzen', 'amount' => null, 'unit' => null, 'category' => 'Sonstiges', 'done' => false, 'source' => 'manual']);

        Topic::factory()->for($event)->create(['title' => 'Ferien', 'notes' => "Wann?\nWohin?"]);

        return $user;
    }

    private function export(User $user): void
    {
        $this->artisan('hostpilot:export', ['--user' => $user->email, '--output' => $this->file])->assertSuccessful();
    }

    public function test_round_trip_moves_everything_to_another_account(): void
    {
        $source = $this->source();
        $target = User::factory()->create(['email' => 'ziel@example.com']);

        $this->export($source);
        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'ziel@example.com', '--force' => true])
            ->expectsOutputToContain('1 Event(s), 2 Programmpunkt(e), 1 Rezept(e), 2 Einkaufs-Eintrag/-Eintraege, 1 Thema/Themen und 2 Bild(er)')
            ->assertSuccessful();

        $event = $target->events()->with(['people', 'agendaItems', 'recipes.ingredients', 'shoppingItems', 'topics'])->sole();

        $this->assertSame('Chalet Revival', $event->title);
        $this->assertSame('2026-10-24', $event->date->format('Y-m-d'));
        $this->assertSame('10:00', $event->time);
        $this->assertSame('Bei Mira', $event->location);
        $this->assertSame(3, $event->guests);
        $this->assertSame(5, $event->cover_style);

        // helpers and the agenda's reference to them
        $this->assertSame(['Carmen', 'Alle'], $event->people->pluck('name')->all());
        $aufstehen = $event->agendaItems->firstWhere('title', 'Aufstehen');
        $this->assertSame('Carmen', $event->people->firstWhere('id', $aufstehen->person_id)->name);
        $this->assertNull($event->agendaItems->firstWhere('title', 'Ankunft')->person_id);

        $recipe = $event->recipes->sole();
        $this->assertSame(['Dünsten.', 'Eier stocken lassen.'], $recipe->steps);
        $this->assertSame([800.5, 4.0], $recipe->ingredients->sortByDesc('amount')->pluck('amount')->all());
        $this->assertSame(['Eier', 'Pelati'], $recipe->ingredients->pluck('name')->all());

        $this->assertTrue($event->shoppingItems->firstWhere('name', 'Eier')->done);
        $this->assertNull($event->shoppingItems->firstWhere('name', 'Kerzen')->amount);
        $this->assertSame("Wann?\nWohin?", $event->topics->sole()->notes);

        // images were re-created under new names and have the same content
        $this->assertNotSame('events/cover.png', $event->cover_path);
        $this->assertSame($this->png(), Storage::disk('public')->get($event->cover_path));
        $this->assertSame($this->png(), Storage::disk('public')->get($recipe->cover_path));

        // the source account is untouched
        $this->assertSame(1, $source->events()->count());
        Storage::disk('public')->assertExists('events/cover.png');
    }

    public function test_export_contains_no_account_data(): void
    {
        $source = $this->source();

        $this->export($source);

        $json = (string) file_get_contents($this->file);
        $this->assertStringNotContainsString('quelle@example.com', $json);
        $this->assertStringNotContainsString('geheim-passwort-123', $json);
        $this->assertStringNotContainsString('password', $json);
        $this->assertStringContainsString('"format": "hostpilot-export"', $json);
        $this->assertStringContainsString('Chalet Revival', $json);
    }

    public function test_export_only_contains_the_events_of_that_account(): void
    {
        $source = $this->source();
        Event::factory()->create(['title' => 'Fremdes Event']);

        $this->export($source);

        $this->assertStringNotContainsString('Fremdes Event', (string) file_get_contents($this->file));
    }

    public function test_events_that_already_exist_are_skipped(): void
    {
        $source = $this->source();
        $target = User::factory()->create(['email' => 'ziel@example.com']);
        $this->export($source);

        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'ziel@example.com', '--force' => true])->assertSuccessful();
        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'ziel@example.com', '--force' => true])
            ->expectsOutputToContain('uebersprungen (gibt es schon): Chalet Revival (2026-10-24)')
            ->expectsOutputToContain('Nichts zu importieren.')
            ->assertSuccessful();

        $this->assertSame(1, $target->events()->count());

        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'ziel@example.com', '--force' => true, '--allow-duplicates' => true])->assertSuccessful();

        $this->assertSame(2, $target->events()->count());
    }

    public function test_dry_run_writes_nothing(): void
    {
        $source = $this->source();
        $target = User::factory()->create(['email' => 'ziel@example.com']);
        $this->export($source);
        $filesBefore = count(Storage::disk('public')->allFiles());

        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'ziel@example.com', '--dry-run' => true])
            ->expectsOutputToContain('importieren: Chalet Revival')
            ->expectsOutputToContain('Testlauf: Es wurde nichts geschrieben.')
            ->assertSuccessful();

        $this->assertSame(0, $target->events()->count());
        $this->assertCount($filesBefore, Storage::disk('public')->allFiles());
    }

    public function test_import_asks_for_confirmation_and_can_be_declined(): void
    {
        $source = $this->source();
        $target = User::factory()->create(['email' => 'ziel@example.com']);
        $this->export($source);

        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'ziel@example.com'])
            ->expectsConfirmation('1 Event(s) jetzt importieren?', 'no')
            ->assertFailed();

        $this->assertSame(0, $target->events()->count());
    }

    public function test_unknown_target_account_or_missing_arguments_fail(): void
    {
        $source = $this->source();
        $this->export($source);

        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'gibt-es-nicht@example.com', '--force' => true])
            ->expectsOutputToContain('Bitte zuerst auf der Seite registrieren')
            ->assertFailed();

        $this->artisan('hostpilot:import', ['file' => $this->file])->assertFailed();
        $this->artisan('hostpilot:import', ['file' => '/gibt/es/nicht.json', '--to' => $source->email])->assertFailed();
        $this->artisan('hostpilot:export')->assertFailed();
        $this->artisan('hostpilot:export', ['--user' => 'gibt-es-nicht@example.com'])->assertFailed();
    }

    /**
     * @param  callable(array<string, mixed>): array<string, mixed>  $tamper
     */
    private function assertRejected(callable $tamper, string $message): void
    {
        // may run several times per test: start from a clean slate each time
        User::query()->delete();
        $source = $this->source();
        $target = User::factory()->create(['email' => 'ziel@example.com']);
        $this->export($source);

        $data = json_decode((string) file_get_contents($this->file), true);
        File::put($this->file, json_encode($tamper($data)));
        $filesBefore = count(Storage::disk('public')->allFiles());

        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'ziel@example.com', '--force' => true])
            ->expectsOutputToContain($message)
            ->expectsOutputToContain('Es wurde nichts veraendert.')
            ->assertFailed();

        $this->assertSame(0, $target->events()->count());
        $this->assertCount($filesBefore, Storage::disk('public')->allFiles());
    }

    public function test_invalid_files_are_rejected_without_changing_anything(): void
    {
        $this->assertRejected(function (array $d) {
            $d['format'] = 'anderes-format';

            return $d;
        }, 'format');

        $this->assertRejected(function (array $d) {
            $d['events'][0]['recipes'][0]['course'] = 'Mitternachtssnack';

            return $d;
        }, 'course');

        $this->assertRejected(function (array $d) {
            $d['events'][0]['guests'] = 0;

            return $d;
        }, 'guests');

        $this->assertRejected(function (array $d) {
            $d['events'][0]['agenda'][0]['person'] = 9;

            return $d;
        }, 'unbekannte Person');
    }

    public function test_images_must_be_valid_and_real_images(): void
    {
        $this->assertRejected(function (array $d) {
            $d['events'][0]['cover']['data'] = '!!!kein-base64!!!';

            return $d;
        }, 'ungueltige Kodierung');

        $this->assertRejected(function (array $d) {
            $d['events'][0]['recipes'][0]['cover'] = ['mime' => 'image/png', 'data' => base64_encode('<?php echo "hallo";')];

            return $d;
        }, 'kein erlaubtes Bild');
    }

    public function test_a_file_that_is_not_json_is_rejected(): void
    {
        $target = User::factory()->create(['email' => 'ziel@example.com']);
        File::put($this->file, 'das ist kein json');

        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'ziel@example.com', '--force' => true])
            ->expectsOutputToContain('kein gueltiges JSON')
            ->assertFailed();

        $this->assertSame(0, $target->events()->count());
    }

    public function test_paths_in_the_file_are_never_used(): void
    {
        $source = $this->source();
        $target = User::factory()->create(['email' => 'ziel@example.com']);
        $this->export($source);

        $data = json_decode((string) file_get_contents($this->file), true);
        $data['events'][0]['cover_path'] = '../../.env';
        $data['events'][0]['cover']['path'] = '../../.env';
        File::put($this->file, json_encode($data));

        $this->artisan('hostpilot:import', ['file' => $this->file, '--to' => 'ziel@example.com', '--force' => true])->assertSuccessful();

        $path = $target->events()->sole()->cover_path;
        $this->assertMatchesRegularExpression('#^events/[A-Za-z0-9]{40}\.png$#', $path);
    }
}
