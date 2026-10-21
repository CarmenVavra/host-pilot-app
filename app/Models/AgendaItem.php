<?php

namespace App\Models;

use Database\Factories\AgendaItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single entry of an event's agenda (Ablaufplan).
 *
 * @property int $id
 * @property int $event_id
 * @property int|null $person_id
 * @property string $time
 * @property string $title
 * @property string|null $note
 */
class AgendaItem extends Model
{
    /** @use HasFactory<AgendaItemFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['person_id', 'time', 'title', 'note'];

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<Person, $this> */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
