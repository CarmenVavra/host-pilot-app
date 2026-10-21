<?php

namespace App\Http\Requests;

use App\Enums\ShoppingCategory;
use App\Enums\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a manually added shopping list entry.
 */
class ShoppingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('event'));
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('amount'))) {
            $this->merge(['amount' => str_replace(',', '.', trim($this->input('amount')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'unit' => ['nullable', Rule::enum(Unit::class)],
            'category' => ['required', Rule::enum(ShoppingCategory::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Artikel',
            'amount' => 'Menge',
            'unit' => 'Einheit',
            'category' => 'Kategorie',
        ];
    }
}
