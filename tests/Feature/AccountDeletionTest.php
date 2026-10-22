<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\ShoppingItem;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_offers_account_deletion_with_confirmation(): void
    {
        $this->actingAs(User::factory()->create())->get(route('profile.edit'))
            ->assertStatus(200)
            ->assertSee('Konto löschen')
            ->assertSee('Passwort zur Bestätigung')
            ->assertSee('data-confirm="Konto und alle Daten endgültig löschen?"', false)
            ->assertSee('data-confirm-button="Konto löschen"', false);
    }

    public function test_account_and_all_data_are_deleted(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create(['cover_path' => 'events/cover.jpg']);
        Storage::disk('public')->put('events/cover.jpg', 'x');
        $recipe = Recipe::factory()->for($event)->create(['cover_path' => 'recipes/dish.jpg']);
        Storage::disk('public')->put('recipes/dish.jpg', 'x');
        $ingredient = Ingredient::factory()->for($recipe)->create();
        $agenda = AgendaItem::factory()->for($event)->create();
        $topic = Topic::factory()->for($event)->create();
        $shopping = ShoppingItem::factory()->for($event)->create();
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'x', 'created_at' => now()]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['delete_password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Dein Konto und alle Daten wurden gelöscht.');

        $this->assertGuest();
        $this->assertModelMissing($user);
        $this->assertModelMissing($event);
        $this->assertModelMissing($recipe);
        $this->assertModelMissing($ingredient);
        $this->assertModelMissing($agenda);
        $this->assertModelMissing($topic);
        $this->assertModelMissing($shopping);
        $this->assertDatabaseCount('people', 0);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        Storage::disk('public')->assertMissing('events/cover.jpg');
        Storage::disk('public')->assertMissing('recipes/dish.jpg');
    }

    public function test_uploaded_cover_images_are_removed_from_disk(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('events.store'), [
            'title' => 'Mit Bild',
            'date' => '2026-12-01',
            'guests' => 4,
            'cover' => UploadedFile::fake()->image('cover.jpg'),
        ]);
        $path = Event::firstOrFail()->cover_path;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($user)->delete(route('profile.destroy'), ['delete_password' => 'password']);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_wrong_password_keeps_the_account(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['delete_password' => 'falsch'])
            ->assertSessionHasErrorsIn('deleteAccount', 'delete_password');

        $this->assertAuthenticatedAs($user);
        $this->assertModelExists($user);
        $this->assertModelExists($event);
    }

    public function test_password_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete(route('profile.destroy'))
            ->assertSessionHasErrorsIn('deleteAccount', 'delete_password');

        $this->assertModelExists($user);
    }

    public function test_other_accounts_are_not_affected(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $otherEvent = Event::factory()->for($other)->create();

        $this->actingAs($user)->delete(route('profile.destroy'), ['delete_password' => 'password']);

        $this->assertModelMissing($user);
        $this->assertModelExists($other);
        $this->assertModelExists($otherEvent);
    }

    public function test_guests_cannot_delete_accounts(): void
    {
        $user = User::factory()->create();

        $this->delete(route('profile.destroy'), ['delete_password' => 'password'])->assertRedirect(route('login'));

        $this->assertModelExists($user);
    }

    public function test_deleted_user_cannot_log_in_again(): void
    {
        $user = User::factory()->create(['email' => 'weg@example.com']);

        $this->actingAs($user)->delete(route('profile.destroy'), ['delete_password' => 'password']);

        $this->post(route('login'), ['email' => 'weg@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
