<?php

namespace App\Http\Requests;

use App\Enums\Course;
use App\Enums\ShoppingCategory;
use App\Enums\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a recipe including its ingredient rows and preparation steps.
 */
class RecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('event'));
    }

    /**
     * Normalises decimal commas («1,5») in ingredient amounts.
     */
    protected function prepareForValidation(): void
    {
        $rows = $this->input('ingredients');

        if (! is_array($rows)) {
            return;
        }

        $this->merge([
            'ingredients' => array_map(function ($row) {
                if (is_array($row) && isset($row['amount']) && is_string($row['amount'])) {
                    $row['amount'] = str_replace(',', '.', trim($row['amount']));
                }

                return $row;
            }, $rows),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'course' => ['required', Rule::enum(Course::class)],
            'portions' => ['required', 'integer', 'min:1', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'diet' => ['nullable', 'string', 'max:60'],
            'steps' => ['nullable', 'string', 'max:10000'],
            'cover' => ['nullable', 'image', 'max:5120'],
            'remove_cover' => ['sometimes', 'boolean'],
            'ingredients' => ['nullable', 'array', 'max:100'],
            'ingredients.*.name' => ['nullable', 'string', 'max:80'],
            'ingredients.*.amount' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'ingredients.*.unit' => ['required_with:ingredients.*.name', 'nullable', Rule::enum(Unit::class)],
            'ingredients.*.category' => ['required_with:ingredients.*.name', 'nullable', Rule::enum(ShoppingCategory::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'Titel',
            'course' => 'Gang',
            'portions' => 'Portionen',
            'duration_minutes' => 'Dauer',
            'diet' => 'Ernährungsform',
            'steps' => 'Zubereitung',
            'cover' => 'Bild',
            'ingredients.*.name' => 'Zutat',
            'ingredients.*.amount' => 'Menge',
            'ingredients.*.unit' => 'Einheit',
            'ingredients.*.category' => 'Kategorie',
        ];
    }
}
