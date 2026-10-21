<?php

namespace App\Models;

use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A helper who can be made responsible for agenda items of an event.
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string $initials
 * @property int $color
 */
class Person extends Model
{
    /** @use HasFactory<PersonFactory> */
    use HasFactory;

    /** Number of avatar colour schemes (see .avatar--c{n} in app.css). */
    public const COLORS = 4;

    /** @var list<string> */
    protected $fillable = ['name', 'initials', 'color'];

    protected function casts(): array
    {
        return ['color' => 'integer'];
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return HasMany<AgendaItem, $this> */
    public function agendaItems(): HasMany
    {
        return $this->hasMany(AgendaItem::class);
    }

    /**
     * «Mira Keller» → «MK», «Jonas» → «JO».
     */
    public static function initialsFor(string $name): string
    {
        $words = preg_split('/[\s\-]+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) >= 2) {
            return Str::upper(Str::substr($words[0], 0, 1).Str::substr($words[count($words) - 1], 0, 1));
        }

        return Str::upper(Str::substr($words[0] ?? '?', 0, 2));
    }
}
