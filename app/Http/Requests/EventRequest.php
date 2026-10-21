<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates creating and updating an event.
 */
class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            ? (bool) $this->user()?->can('update', $event)
            : $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:120'],
            'guests' => ['required', 'integer', 'min:1', 'max:500'],
            'cover' => ['nullable', 'image', 'max:5120'],
            'remove_cover' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'Titel',
            'date' => 'Datum',
            'time' => 'Uhrzeit',
            'location' => 'Ort',
            'guests' => 'Gäste',
            'cover' => 'Cover-Bild',
        ];
    }
}
