<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->event = Event::factory()->for($this->user)->create(['date' => '2026-11-23', 'time' => '10:00']);
    }

    public function test_timeline_is_sorted_by_time_and_shows_responsible_person(): void
    {
        $mira = Person::factory()->for($this->event)->create(['name' => 'Mira', 'initials' => 'MK']);
        $this->event->agendaItems()->create(['time' => '12:00', 'title' => 'Torte & Kerzen']);
        $this->event->agendaItems()->create(['time' => '08:30', 'title' => 'Einkauf abholen', 'person_id' => $mira->id]);

        $this->actingAs($this->user)->get(route('events.agenda.index', $this->event))
            ->assertStatus(200)
            ->assertSee('Mo, 23. November')
            ->assertSeeInOrder(['08:30', 'Einkauf abholen', '12:00', 'Torte &amp; Kerzen'], false)
            ->assertSee('MK')
            ->assertSee('Neuer Programmpunkt');
    }

    public function test_green_marker_is_explained_by_a_legend(): void
    {
        $this->event->agendaItems()->create(['time' => '09:00', 'title' => 'Vorbereiten']);
        $this->event->agendaItems()->create(['time' => '10:00', 'title' => 'Ankunft Toni']);

        $this->actingAs($this->user)->get(route('events.agenda.index', $this->event))
            ->assertStatus(200)
            ->assertSee('Grün markiert: Beginn des Events (10:00)')
            ->assertSeeInOrder(['10:00', '(Beginn des Events (10:00))', 'Ankunft Toni']);
    }

    public function test_no_legend_without_marked_item(): void
    {
        $this->event->agendaItems()->create(['time' => '09:00', 'title' => 'Vorbereiten']);

        $this->actingAs($this->user)->get(route('events.agenda.index', $this->event))
            ->assertStatus(200)
            ->assertDontSee('Grün markiert');
    }

    public function test_item_can_be_added(): void
    {
        $person = Person::factory()->for($this->event)->create();

        $this->actingAs($this->user)->post(route('events.agenda.store', $this->event), [
            'time' => '14:30',
            'title' => 'Brunch aufräumen',
            'note' => 'Alle helfen',
            'person_id' => $person->id,
        ])->assertRedirect(route('events.agenda.index', $this->event));

        $this->assertDatabaseHas('agenda_items', [
            'event_id' => $this->event->id,
            'time' => '14:30',
            'title' => 'Brunch aufräumen',
            'person_id' => $person->id,
        ]);
    }

    public function test_item_validation_and_person_must_belong_to_event(): void
    {
        $foreignPerson = Person::factory()->create();

        $this->actingAs($this->user)->post(route('events.agenda.store', $this->event), [
            'time' => '25:99',
            'title' => '',
            'person_id' => $foreignPerson->id,
        ])->assertSessionHasErrors(['time', 'title', 'person_id']);

        $this->assertDatabaseCount('agenda_items', 0);
    }

    public function test_item_can_be_edited_and_deleted(): void
    {
        $item = AgendaItem::factory()->for($this->event)->create(['title' => 'Alt']);

        $this->actingAs($this->user)->get(route('events.agenda.edit', [$this->event, $item]))
            ->assertStatus(200)
            ->assertSee('Programmpunkt ändern');

        $this->actingAs($this->user)->put(route('events.agenda.update', [$this->event, $item]), [
            'time' => '09:00',
            'title' => 'Neu',
        ])->assertRedirect(route('events.agenda.index', $this->event));

        $this->assertSame('Neu', $item->fresh()->title);

        $this->actingAs($this->user)->delete(route('events.agenda.destroy', [$this->event, $item]))
            ->assertRedirect(route('events.agenda.index', $this->event));

        $this->assertModelMissing($item);
    }

    public function test_items_of_another_event_are_not_reachable_through_this_event(): void
    {
        $otherEvent = Event::factory()->for($this->user)->create();
        $item = AgendaItem::factory()->for($otherEvent)->create();

        $this->actingAs($this->user)->delete(route('events.agenda.destroy', [$this->event, $item]))->assertNotFound();
        $this->assertModelExists($item);
    }

    public function test_person_can_be_added(): void
    {
        $this->actingAs($this->user)
            ->from(route('events.agenda.index', $this->event))
            ->post(route('events.people.store', $this->event), ['name' => 'Jonas Brunner'])
            ->assertRedirect(route('events.agenda.index', $this->event));

        $this->assertDatabaseHas('people', ['event_id' => $this->event->id, 'name' => 'Jonas Brunner', 'initials' => 'JB']);
    }

    public function test_foreign_hosts_cannot_add_items(): void
    {
        $other = User::factory()->create();

        $this->actingAs($other)->post(route('events.agenda.store', $this->event), ['time' => '10:00', 'title' => 'X'])
            ->assertForbidden();
    }
}
