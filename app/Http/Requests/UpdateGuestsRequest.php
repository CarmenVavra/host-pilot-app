<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the guest stepper on the shopping list.
 */
class UpdateGuestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('event'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'guests' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return ['guests' => 'Gäste'];
    }
}
