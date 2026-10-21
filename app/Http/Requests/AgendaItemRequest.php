<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates an agenda item (Programmpunkt). The responsible person must belong to the same event.
 */
class AgendaItemRequest extends FormRequest
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
        /** @var Event $event */
        $event = $this->route('event');

        return [
            'time' => ['required', 'date_format:H:i'],
            'title' => ['required', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:255'],
            'person_id' => ['nullable', 'integer', Rule::exists('people', 'id')->where('event_id', $event->id)],
        ];
    }

    public function attributes(): array
    {
        return [
            'time' => 'Uhrzeit',
            'title' => 'Titel',
            'note' => 'Notiz',
            'person_id' => 'Verantwortlich',
        ];
    }
}
