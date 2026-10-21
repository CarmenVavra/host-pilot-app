<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->event = Event::factory()->for($this->user)->create(['title' => 'Brunch']);
    }

    public function test_empty_state_is_shown_without_topics(): void
    {
        $this->actingAs($this->user)->get(route('events.topics.index', $this->event))
            ->assertStatus(200)
            ->assertSee('Noch keine Themen')
            ->assertSee('Neues Thema');
    }

    public function test_topics_are_listed_with_notes(): void
    {
        Topic::factory()->for($this->event)->create(['title' => 'Sommerferien', 'notes' => "Wann?\nWohin?"]);
        Topic::factory()->for($this->event)->create(['title' => 'Geschenk für Lena']);
        Topic::factory()->create(['title' => 'Fremdes Thema']);

        $this->actingAs($this->user)->get(route('events.topics.index', $this->event))
            ->assertStatus(200)
            ->assertSee('2 Themen')
            ->assertSeeInOrder(['Sommerferien', 'Wann?', 'Wohin?', 'Geschenk für Lena'])
            ->assertDontSee('Fremdes Thema');
    }

    public function test_topic_can_be_created(): void
    {
        $this->actingAs($this->user)->get(route('events.topics.create', $this->event))
            ->assertStatus(200)
            ->assertSee('Überschrift');

        $this->actingAs($this->user)->post(route('events.topics.store', $this->event), [
            'title' => 'Sommerferien gemeinsam planen',
            'notes' => 'Termine abgleichen',
        ])->assertRedirect(route('events.topics.index', $this->event));

        $this->assertDatabaseHas('topics', [
            'event_id' => $this->event->id,
            'title' => 'Sommerferien gemeinsam planen',
            'notes' => 'Termine abgleichen',
        ]);
    }

    public function test_notes_are_optional_and_title_is_required(): void
    {
        $this->actingAs($this->user)->post(route('events.topics.store', $this->event), ['title' => 'Nur Überschrift'])
            ->assertSessionHasNoErrors();

        $this->actingAs($this->user)->post(route('events.topics.store', $this->event), ['title' => '', 'notes' => str_repeat('x', 5001)])
            ->assertSessionHasErrors(['title', 'notes']);

        $this->assertDatabaseCount('topics', 1);
    }

    public function test_topic_can_be_edited(): void
    {
        $topic = Topic::factory()->for($this->event)->create(['title' => 'Alt']);

        $this->actingAs($this->user)->get(route('events.topics.edit', [$this->event, $topic]))
            ->assertStatus(200)
            ->assertSee('Thema bearbeiten')
            ->assertSee('value="Alt"', false);

        $this->actingAs($this->user)->put(route('events.topics.update', [$this->event, $topic]), [
            'title' => 'Neu',
            'notes' => 'Ergänzt',
        ])->assertRedirect(route('events.topics.index', $this->event));

        $this->assertSame('Neu', $topic->fresh()->title);
        $this->assertSame('Ergänzt', $topic->fresh()->notes);
    }

    public function test_topic_can_be_deleted_with_confirmation(): void
    {
        $topic = Topic::factory()->for($this->event)->create(['title' => 'Weg damit']);

        $this->actingAs($this->user)->get(route('events.topics.index', $this->event))
            ->assertSee('data-confirm="Thema «Weg damit» löschen?"', false);

        $this->actingAs($this->user)->delete(route('events.topics.destroy', [$this->event, $topic]))
            ->assertRedirect(route('events.topics.index', $this->event));

        $this->assertModelMissing($topic);
    }

    public function test_topics_are_deleted_with_their_event(): void
    {
        $topic = Topic::factory()->for($this->event)->create();

        $this->actingAs($this->user)->delete(route('events.destroy', $this->event));

        $this->assertModelMissing($topic);
    }

    public function test_hub_shows_topics_card(): void
    {
        Topic::factory()->for($this->event)->create(['title' => 'Sommerferien']);

        $this->actingAs($this->user)->get(route('events.show', $this->event))
            ->assertStatus(200)
            ->assertSee('1 Thema · Sommerferien')
            ->assertSee(route('events.topics.index', $this->event));
    }

    public function test_topic_of_other_event_returns_404(): void
    {
        $topic = Topic::factory()->for(Event::factory()->for($this->user))->create();

        $this->actingAs($this->user)->get(route('events.topics.edit', [$this->event, $topic]))->assertNotFound();
        $this->actingAs($this->user)->delete(route('events.topics.destroy', [$this->event, $topic]))->assertNotFound();

        $this->assertModelExists($topic);
    }

    public function test_foreign_hosts_cannot_access_topics(): void
    {
        $other = User::factory()->create();
        $topic = Topic::factory()->for($this->event)->create();

        $this->actingAs($other)->get(route('events.topics.index', $this->event))->assertForbidden();
        $this->actingAs($other)->post(route('events.topics.store', $this->event), ['title' => 'X'])->assertForbidden();
        $this->actingAs($other)->put(route('events.topics.update', [$this->event, $topic]), ['title' => 'X'])->assertForbidden();
        $this->actingAs($other)->delete(route('events.topics.destroy', [$this->event, $topic]))->assertForbidden();

        $this->assertModelExists($topic);
    }
}
