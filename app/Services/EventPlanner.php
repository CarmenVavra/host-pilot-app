<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Creates, updates and deletes events including their cover photo.
 */
class EventPlanner
{
    public function __construct(
        private readonly CoverImageStore $covers,
        private readonly ShoppingListGenerator $shopping,
    ) {}

    /**
     * Creates the event and the default helpers: the host and «Alle».
     *
     * @param  array<string, mixed>  $data  validated data from EventRequest
     */
    public function create(User $host, array $data, ?UploadedFile $cover): Event
    {
        return DB::transaction(function () use ($host, $data, $cover) {
            $event = $host->events()->make($this->attributes($data));
            $event->cover_style = $host->events()->count() % Event::COVER_STYLES;
            $event->cover_path = $this->covers->replace($cover, null, 'events');
            $event->save();

            $event->people()->createMany([
                ['name' => $host->name, 'initials' => $host->initials(), 'color' => 0],
                ['name' => 'Alle', 'initials' => 'AL', 'color' => 3],
            ]);

            return $event;
        });
    }

    /**
     * @param  array<string, mixed>  $data  validated data from EventRequest
     */
    public function update(Event $event, array $data, ?UploadedFile $cover, bool $removeCover): Event
    {
        $guestsChanged = (int) $data['guests'] !== $event->guests;

        $event->fill($this->attributes($data));
        $event->cover_path = $this->covers->replace($cover, $event->cover_path, 'events', $removeCover);
        $event->save();

        if ($guestsChanged) {
            $this->shopping->refreshIfGenerated($event);
        }

        return $event;
    }

    public function changeGuests(Event $event, int $guests): void
    {
        $event->update(['guests' => $guests]);
        $this->shopping->refreshIfGenerated($event);
    }

    public function delete(Event $event): void
    {
        $event->load('recipes');

        $event->recipes->each(fn ($recipe) => $this->covers->delete($recipe->cover_path));
        $this->covers->delete($event->cover_path);

        $event->delete();
    }

    public function addPerson(Event $event, string $name): Person
    {
        return $event->people()->create([
            'name' => trim($name),
            'initials' => Person::initialsFor($name),
            'color' => $event->people()->count() % Person::COLORS,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'date' => $data['date'],
            'time' => $data['time'] ?? null,
            'location' => $data['location'] ?? null,
            'guests' => (int) $data['guests'],
        ];
    }
}
