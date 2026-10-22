<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Exports all events of one user (with agenda, recipes, shopping list, topics,
 * helpers and cover images) into a single JSON-serialisable structure.
 *
 * The export contains no account data (no e-mail, no password). Images are
 * embedded as base64 so one file is enough to move everything to another server.
 */
class AccountExporter
{
    public const FORMAT = 'hostpilot-export';

    public const VERSION = 1;

    /**
     * @return array<string, mixed>
     */
    public function export(User $user): array
    {
        $events = $user->events()
            ->with(['people', 'agendaItems', 'recipes.ingredients', 'shoppingItems', 'topics'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        return [
            'format' => self::FORMAT,
            'version' => self::VERSION,
            'exported_at' => now()->toIso8601String(),
            'events' => $events->map(fn (Event $event) => $this->event($event))->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function event(Event $event): array
    {
        // agenda items refer to helpers by their position in the "people" list
        $position = $event->people->values()->pluck('id')->flip();

        return [
            'title' => $event->title,
            'date' => $event->date->format('Y-m-d'),
            'time' => $event->time,
            'location' => $event->location,
            'guests' => $event->guests,
            'cover_style' => $event->cover_style,
            'cover' => $this->cover($event->cover_path),
            'people' => $event->people->map(fn ($person) => [
                'name' => $person->name,
                'initials' => $person->initials,
                'color' => $person->color,
            ])->values()->all(),
            'agenda' => $event->agendaItems->map(fn ($item) => [
                'time' => $item->time,
                'title' => $item->title,
                'note' => $item->note,
                'person' => $item->person_id === null ? null : ($position[$item->person_id] ?? null),
            ])->values()->all(),
            'recipes' => $event->recipes->map(fn (Recipe $recipe) => [
                'title' => $recipe->title,
                'course' => $recipe->course,
                'portions' => $recipe->portions,
                'duration_minutes' => $recipe->duration_minutes,
                'diet' => $recipe->diet,
                'steps' => $recipe->steps ?? [],
                'cover_style' => $recipe->cover_style,
                'cover' => $this->cover($recipe->cover_path),
                'ingredients' => $recipe->ingredients->map(fn ($ingredient) => [
                    'name' => $ingredient->name,
                    'amount' => $ingredient->amount,
                    'unit' => $ingredient->unit,
                    'category' => $ingredient->category,
                    'position' => $ingredient->position,
                ])->values()->all(),
            ])->values()->all(),
            'shopping' => $event->shoppingItems->map(fn ($item) => [
                'name' => $item->name,
                'amount' => $item->amount,
                'unit' => $item->unit,
                'category' => $item->category,
                'done' => $item->done,
                'source' => $item->source,
            ])->values()->all(),
            'topics' => $event->topics->map(fn ($topic) => [
                'title' => $topic->title,
                'notes' => $topic->notes,
            ])->values()->all(),
        ];
    }

    /**
     * @return array{mime: string, data: string}|null
     */
    private function cover(?string $path): ?array
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $content = (string) Storage::disk('public')->get($path);
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);

        return ['mime' => $mime, 'data' => base64_encode($content)];
    }
}
