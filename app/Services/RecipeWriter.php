<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Recipe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Persists a recipe together with its ingredients and preparation steps.
 */
class RecipeWriter
{
    public function __construct(private readonly CoverImageStore $covers) {}

    /**
     * @param  array<string, mixed>  $data  validated data from RecipeRequest
     */
    public function create(Event $event, array $data, ?UploadedFile $cover): Recipe
    {
        return DB::transaction(function () use ($event, $data, $cover) {
            $recipe = $event->recipes()->make($this->attributes($data));
            $recipe->cover_style = $event->recipes()->count() % Event::COVER_STYLES;
            $recipe->cover_path = $this->covers->replace($cover, null, 'recipes');
            $recipe->save();

            $this->syncIngredients($recipe, $data['ingredients'] ?? []);

            return $recipe;
        });
    }

    /**
     * @param  array<string, mixed>  $data  validated data from RecipeRequest
     */
    public function update(Recipe $recipe, array $data, ?UploadedFile $cover, bool $removeCover): Recipe
    {
        return DB::transaction(function () use ($recipe, $data, $cover, $removeCover) {
            $recipe->fill($this->attributes($data));
            $recipe->cover_path = $this->covers->replace($cover, $recipe->cover_path, 'recipes', $removeCover);
            $recipe->save();

            $this->syncIngredients($recipe, $data['ingredients'] ?? []);

            return $recipe;
        });
    }

    public function delete(Recipe $recipe): void
    {
        $this->covers->delete($recipe->cover_path);
        $recipe->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'course' => $data['course'],
            'portions' => (int) $data['portions'],
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'diet' => $data['diet'] ?? null,
            'steps' => $this->parseSteps($data['steps'] ?? null),
        ];
    }

    /**
     * One step per line; blank lines and leading numbering («1.», «2)») are dropped.
     *
     * @return list<string>
     */
    private function parseSteps(?string $steps): array
    {
        $lines = preg_split('/\R/u', (string) $steps) ?: [];

        return array_values(array_filter(array_map(
            fn (string $line) => trim((string) preg_replace('/^\s*\d+[.)]\s*/u', '', $line)),
            $lines,
        ), fn (string $line) => $line !== ''));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncIngredients(Recipe $recipe, array $rows): void
    {
        $recipe->ingredients()->delete();

        $position = 0;

        foreach ($rows as $row) {
            if (blank($row['name'] ?? null)) {
                continue;
            }

            $recipe->ingredients()->create([
                'name' => trim($row['name']),
                'amount' => $row['amount'] ?? null,
                'unit' => $row['unit'],
                'category' => $row['category'],
                'position' => $position++,
            ]);
        }
    }
}
