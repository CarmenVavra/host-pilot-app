<?php

namespace App\Services;

use App\Enums\Course;
use App\Enums\ShoppingCategory;
use App\Enums\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use JsonException;
use Throwable;

/**
 * Imports an export file (see AccountExporter) into an existing account.
 *
 * Everything is validated first; only then a single transaction writes the
 * data. Images are re-created under new random names (no path from the file is
 * ever used) and removed again if the import fails.
 */
class AccountImporter
{
    /** Image types accepted for covers, with the file extension to store them with. */
    private const IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    private const MAX_IMAGE_BYTES = 6 * 1024 * 1024;

    /**
     * Parses and validates the file content and returns the events.
     *
     * @return list<array<string, mixed>>
     *
     * @throws InvalidExportException
     */
    public function read(string $json): array
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidExportException('Die Datei ist kein gueltiges JSON.');
        }

        if (! is_array($data)) {
            throw new InvalidExportException('Die Datei hat nicht das erwartete Format.');
        }

        $validator = Validator::make($data, $this->rules());

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $more = count($errors) > 10 ? "\n... und ".(count($errors) - 10).' weitere Fehler' : '';

            throw new InvalidExportException(implode("\n", array_slice($errors, 0, 10)).$more);
        }

        /** @var list<array<string, mixed>> $events */
        $events = array_values($data['events']);

        foreach ($events as $index => $event) {
            $this->checkReferencesAndImages($event, $index);
        }

        return $events;
    }

    /**
     * Splits the events into those to import and titles of those that already exist
     * (same title and date) in the target account.
     *
     * @param  list<array<string, mixed>>  $events
     * @return array{import: list<array<string, mixed>>, skipped: list<string>}
     */
    public function plan(User $target, array $events, bool $allowDuplicates): array
    {
        $existing = $target->events()->get(['title', 'date'])
            ->mapWithKeys(fn ($event) => [$this->key($event->title, $event->date->format('Y-m-d')) => true]);

        $import = [];
        $skipped = [];

        foreach ($events as $event) {
            $label = $event['title'].' ('.$event['date'].')';

            if (! $allowDuplicates && $existing->has($this->key($event['title'], $event['date']))) {
                $skipped[] = $label;

                continue;
            }

            $import[] = $event;
        }

        return ['import' => $import, 'skipped' => $skipped];
    }

    /**
     * Writes the events into the target account.
     *
     * @param  list<array<string, mixed>>  $events
     * @return array{events: int, agenda: int, recipes: int, shopping: int, topics: int, images: int}
     */
    public function import(User $target, array $events): array
    {
        $counts = ['events' => 0, 'agenda' => 0, 'recipes' => 0, 'shopping' => 0, 'topics' => 0, 'images' => 0];
        $written = [];

        try {
            DB::transaction(function () use ($target, $events, &$counts, &$written) {
                foreach ($events as $data) {
                    $event = $target->events()->make([
                        'title' => $data['title'],
                        'date' => $data['date'],
                        'time' => $data['time'] ?? null,
                        'location' => $data['location'] ?? null,
                        'guests' => $data['guests'],
                        'cover_style' => $data['cover_style'] ?? 0,
                    ]);
                    $event->cover_path = $this->storeImage($data['cover'] ?? null, 'events', $written);
                    $event->save();
                    $counts['events']++;

                    $people = [];
                    foreach ($data['people'] as $position => $person) {
                        $people[$position] = $event->people()->create([
                            'name' => $person['name'],
                            'initials' => $person['initials'],
                            'color' => $person['color'],
                        ])->id;
                    }

                    foreach ($data['agenda'] as $item) {
                        $event->agendaItems()->create([
                            'time' => $item['time'],
                            'title' => $item['title'],
                            'note' => $item['note'] ?? null,
                            'person_id' => isset($item['person']) ? $people[$item['person']] : null,
                        ]);
                        $counts['agenda']++;
                    }

                    foreach ($data['recipes'] as $recipeData) {
                        $recipe = $event->recipes()->make([
                            'title' => $recipeData['title'],
                            'course' => $recipeData['course'],
                            'portions' => $recipeData['portions'],
                            'duration_minutes' => $recipeData['duration_minutes'] ?? null,
                            'diet' => $recipeData['diet'] ?? null,
                            'steps' => $recipeData['steps'] ?? [],
                            'cover_style' => $recipeData['cover_style'] ?? 0,
                        ]);
                        $recipe->cover_path = $this->storeImage($recipeData['cover'] ?? null, 'recipes', $written);
                        $recipe->save();
                        $counts['recipes']++;

                        foreach ($recipeData['ingredients'] as $ingredient) {
                            $recipe->ingredients()->create([
                                'name' => $ingredient['name'],
                                'amount' => $ingredient['amount'] ?? null,
                                'unit' => $ingredient['unit'],
                                'category' => $ingredient['category'],
                                'position' => $ingredient['position'] ?? 0,
                            ]);
                        }
                    }

                    foreach ($data['shopping'] as $item) {
                        $event->shoppingItems()->create([
                            'name' => $item['name'],
                            'amount' => $item['amount'] ?? null,
                            'unit' => $item['unit'] ?? null,
                            'category' => $item['category'],
                            'done' => (bool) $item['done'],
                            'source' => $item['source'],
                        ]);
                        $counts['shopping']++;
                    }

                    foreach ($data['topics'] as $topic) {
                        $event->topics()->create([
                            'title' => $topic['title'],
                            'notes' => $topic['notes'] ?? null,
                        ]);
                        $counts['topics']++;
                    }
                }
            });
        } catch (Throwable $exception) {
            foreach ($written as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        $counts['images'] = count($written);

        return $counts;
    }

    /**
     * @param  array{mime: string, data: string}|null  $cover
     * @param  list<string>  $written  paths of stored images (collected for cleanup)
     */
    private function storeImage(?array $cover, string $directory, array &$written): ?string
    {
        if ($cover === null) {
            return null;
        }

        $content = (string) base64_decode($cover['data'], true);
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
        $path = $directory.'/'.Str::random(40).'.'.self::IMAGE_TYPES[$mime];

        Storage::disk('public')->put($path, $content);
        $written[] = $path;

        return $path;
    }

    /**
     * @param  array<string, mixed>  $event
     *
     * @throws InvalidExportException
     */
    private function checkReferencesAndImages(array $event, int $index): void
    {
        $peopleCount = count($event['people']);

        foreach ($event['agenda'] as $item) {
            if (isset($item['person']) && $item['person'] >= $peopleCount) {
                throw new InvalidExportException("Event {$index}: Ein Programmpunkt verweist auf eine unbekannte Person.");
            }
        }

        $this->checkImage($event['cover'] ?? null, "Event {$index}: Cover-Bild");

        foreach ($event['recipes'] as $recipeIndex => $recipe) {
            $this->checkImage($recipe['cover'] ?? null, "Event {$index}, Rezept {$recipeIndex}: Bild");
        }
    }

    /**
     * @param  array{mime: string, data: string}|null  $cover
     *
     * @throws InvalidExportException
     */
    private function checkImage(?array $cover, string $label): void
    {
        if ($cover === null) {
            return;
        }

        $content = base64_decode($cover['data'], true);

        if ($content === false || $content === '') {
            throw new InvalidExportException("{$label} ist nicht lesbar (ungueltige Kodierung).");
        }

        if (strlen($content) > self::MAX_IMAGE_BYTES) {
            throw new InvalidExportException("{$label} ist groesser als 6 MB.");
        }

        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);

        if (! array_key_exists($mime, self::IMAGE_TYPES)) {
            throw new InvalidExportException("{$label} ist kein erlaubtes Bild (JPG, PNG, WebP oder GIF), erkannt: {$mime}.");
        }
    }

    private function key(string $title, string $date): string
    {
        return mb_strtolower(trim($title)).'|'.$date;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $image = ['nullable', 'array'];

        return [
            'format' => ['required', Rule::in([AccountExporter::FORMAT])],
            'version' => ['required', 'integer', Rule::in([AccountExporter::VERSION])],
            'events' => ['required', 'array', 'max:500'],

            'events.*.title' => ['required', 'string', 'max:120'],
            'events.*.date' => ['required', 'date_format:Y-m-d'],
            'events.*.time' => ['nullable', 'date_format:H:i'],
            'events.*.location' => ['nullable', 'string', 'max:120'],
            'events.*.guests' => ['required', 'integer', 'min:1', 'max:500'],
            'events.*.cover_style' => ['nullable', 'integer', 'min:0', 'max:7'],
            'events.*.cover' => $image,
            'events.*.cover.data' => ['required_with:events.*.cover', 'string'],

            'events.*.people' => ['present', 'array', 'max:100'],
            'events.*.people.*.name' => ['required', 'string', 'max:60'],
            'events.*.people.*.initials' => ['required', 'string', 'max:3'],
            'events.*.people.*.color' => ['required', 'integer', 'min:0', 'max:3'],

            'events.*.agenda' => ['present', 'array', 'max:500'],
            'events.*.agenda.*.time' => ['required', 'date_format:H:i'],
            'events.*.agenda.*.title' => ['required', 'string', 'max:120'],
            'events.*.agenda.*.note' => ['nullable', 'string', 'max:255'],
            'events.*.agenda.*.person' => ['nullable', 'integer', 'min:0'],

            'events.*.recipes' => ['present', 'array', 'max:500'],
            'events.*.recipes.*.title' => ['required', 'string', 'max:120'],
            'events.*.recipes.*.course' => ['required', Rule::enum(Course::class)],
            'events.*.recipes.*.portions' => ['required', 'integer', 'min:1', 'max:500'],
            'events.*.recipes.*.duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'events.*.recipes.*.diet' => ['nullable', 'string', 'max:60'],
            'events.*.recipes.*.steps' => ['nullable', 'array', 'max:200'],
            'events.*.recipes.*.steps.*' => ['string', 'max:2000'],
            'events.*.recipes.*.cover_style' => ['nullable', 'integer', 'min:0', 'max:7'],
            'events.*.recipes.*.cover' => $image,
            'events.*.recipes.*.cover.data' => ['required_with:events.*.recipes.*.cover', 'string'],
            'events.*.recipes.*.ingredients' => ['present', 'array', 'max:200'],
            'events.*.recipes.*.ingredients.*.name' => ['required', 'string', 'max:80'],
            'events.*.recipes.*.ingredients.*.amount' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'events.*.recipes.*.ingredients.*.unit' => ['required', Rule::enum(Unit::class)],
            'events.*.recipes.*.ingredients.*.category' => ['required', Rule::enum(ShoppingCategory::class)],
            'events.*.recipes.*.ingredients.*.position' => ['nullable', 'integer', 'min:0'],

            'events.*.shopping' => ['present', 'array', 'max:1000'],
            'events.*.shopping.*.name' => ['required', 'string', 'max:80'],
            'events.*.shopping.*.amount' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'events.*.shopping.*.unit' => ['nullable', Rule::enum(Unit::class)],
            'events.*.shopping.*.category' => ['required', Rule::enum(ShoppingCategory::class)],
            'events.*.shopping.*.done' => ['required', 'boolean'],
            'events.*.shopping.*.source' => ['required', Rule::in(['recipe', 'manual'])],

            'events.*.topics' => ['present', 'array', 'max:500'],
            'events.*.topics.*.title' => ['required', 'string', 'max:120'],
            'events.*.topics.*.notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
