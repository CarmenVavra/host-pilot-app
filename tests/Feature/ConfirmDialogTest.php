<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmDialogTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_layout_contains_the_custom_confirm_dialog(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create(['title' => 'Brunch']);

        $this->actingAs($user)->get(route('events.show', $event))
            ->assertStatus(200)
            ->assertSee('<dialog class="confirm" id="confirm-dialog"', false)
            ->assertSee('aria-labelledby="confirm-title"', false)
            ->assertSee('Abbrechen')
            ->assertSee('data-confirm="Event «Brunch» mit Ablauf, Rezepten und Einkaufsliste löschen?"', false);
    }

    public function test_delete_forms_ask_for_confirmation(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        AgendaItem::factory()->for($event)->create(['title' => 'Aufräumen']);

        $this->actingAs($user)->get(route('events.agenda.index', $event))
            ->assertStatus(200)
            ->assertSee('data-confirm="Programmpunkt «Aufräumen» löschen?"', false);
    }
}
