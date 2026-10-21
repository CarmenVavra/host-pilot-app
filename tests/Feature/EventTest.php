<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_lists_only_own_upcoming_events_sorted_by_date(): void
    {
        $user = User::factory()->create();
        Event::factory()->for($user)->create(['title' => 'Später', 'date' => now()->addDays(30)->toDateString()]);
        Event::factory()->for($user)->create(['title' => 'Bald', 'date' => now()->addDays(3)->toDateString()]);
        Event::factory()->for($user)->past()->create(['title' => 'Vorbei']);
        Event::factory()->create(['title' => 'Fremdes Event']);

        $this->actingAs($user)->get(route('events.index'))
            ->assertStatus(200)
            ->assertSee('2 Events geplant')
            ->assertSeeInOrder(['Bald', 'Später'])
            ->assertSee('in 3 Tagen')
            ->assertDontSee('event-card__title">Vorbei', false)
            ->assertDontSee('Fremdes Event');
    }

    public function test_guest_count_uses_singular_for_one_guest(): void
    {
        $user = User::factory()->create();
        $single = Event::factory()->for($user)->create(['guests' => 1]);
        Event::factory()->for($user)->create(['guests' => 12]);

        $this->actingAs($user)->get(route('events.index'))
            ->assertSee('1 Gast')
            ->assertSee('12 Gäste')
            ->assertDontSee('1 Gäste')
            ->assertSee('Neues Event erstellen');

        $this->actingAs($user)->get(route('events.show', $single))
            ->assertSee('1 Gast')
            ->assertDontSee('1 Gäste');
    }

    public function test_overview_can_show_past_events(): void
    {
        $user = User::factory()->create();
        Event::factory()->for($user)->past()->create(['title' => 'Vorbei']);

        $this->actingAs($user)->get(route('events.index', ['filter' => 'vergangen']))
            ->assertStatus(200)
            ->assertSee('Vorbei');
    }

    public function test_user_can_create_event_with_cover(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'Mira Keller']);

        $response = $this->actingAs($user)->post(route('events.store'), [
            'title' => 'Geburtstags-Brunch Lena',
            'date' => '2026-11-23',
            'time' => '10:00',
            'location' => 'Bei Mira, Zürich',
            'guests' => 12,
            'cover' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $event = Event::firstOrFail();
        $response->assertRedirect(route('events.show', $event));

        $this->assertSame('Geburtstags-Brunch Lena', $event->title);
        $this->assertSame($user->id, $event->user_id);
        $this->assertNotNull($event->cover_path);
        Storage::disk('public')->assertExists($event->cover_path);

        // Host and «Alle» are created as default helpers.
        $this->assertSame(['MK', 'AL'], $event->people()->pluck('initials')->all());
    }

    public function test_event_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('events.store'), ['title' => '', 'date' => 'morgen', 'guests' => 0])
            ->assertSessionHasErrors(['title', 'date', 'guests']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_hub_shows_sections_and_progress(): void
    {
        Carbon::setTestNow('2026-10-21 09:00');
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create(['title' => 'Brunch', 'date' => '2026-11-23', 'time' => '10:00', 'guests' => 12]);
        $event->agendaItems()->create(['time' => '08:30', 'title' => 'Einkauf abholen']);
        $event->recipes()->create(['title' => 'Shakshuka', 'course' => 'Brunch', 'portions' => 12]);
        $event->shoppingItems()->createMany([
            ['name' => 'Eier', 'category' => 'Milch & Eier', 'done' => true, 'source' => 'recipe'],
            ['name' => 'Feta', 'category' => 'Milch & Eier', 'done' => false, 'source' => 'recipe'],
        ]);

        $this->actingAs($user)->get(route('events.show', $event))
            ->assertStatus(200)
            ->assertSee('in 33 Tagen')
            ->assertSee('Mo, 23. Nov · 10:00')
            ->assertSee('als Nächstes 08:30 Einkauf abholen')
            ->assertSee('1 Rezept · Brunch')
            ->assertSee('1 von 2 erledigt');

        Carbon::setTestNow();
    }

    public function test_user_can_update_and_delete_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $this->actingAs($user)->put(route('events.update', $event), [
            'title' => 'Neuer Titel',
            'date' => '2026-12-01',
            'time' => '19:30',
            'location' => 'Bern',
            'guests' => 20,
        ])->assertRedirect(route('events.show', $event));

        $this->assertSame('Neuer Titel', $event->fresh()->title);
        $this->assertSame(20, $event->fresh()->guests);

        $this->actingAs($user)->delete(route('events.destroy', $event))->assertRedirect(route('events.index'));
        $this->assertModelMissing($event);
    }

    public function test_events_of_other_hosts_are_forbidden(): void
    {
        $user = User::factory()->create();
        $foreign = Event::factory()->create();

        $this->actingAs($user)->get(route('events.show', $foreign))->assertForbidden();
        $this->actingAs($user)->get(route('events.edit', $foreign))->assertForbidden();
        $this->actingAs($user)->put(route('events.update', $foreign), ['title' => 'X', 'date' => '2026-12-01', 'guests' => 2])->assertForbidden();
        $this->actingAs($user)->delete(route('events.destroy', $foreign))->assertForbidden();
        $this->actingAs($user)->get(route('events.agenda.index', $foreign))->assertForbidden();
        $this->actingAs($user)->get(route('events.recipes.index', $foreign))->assertForbidden();
        $this->actingAs($user)->get(route('events.shopping.index', $foreign))->assertForbidden();

        $this->assertModelExists($foreign);
    }
}
