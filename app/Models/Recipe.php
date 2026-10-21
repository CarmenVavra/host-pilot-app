<?php

namespace App\Models;

use App\Enums\Course;
use Database\Factories\RecipeFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * A recipe assigned to an event. Ingredient amounts refer to `portions`
 * and are scaled to the event's number of guests for display and shopping.
 *
 * @property int $id
 * @property int $event_id
 * @property string $title
 * @property string $course
 * @property int $portions
 * @property int|null $duration_minutes
 * @property string|null $diet
 * @property list<string>|null $steps
 * @property int $cover_style
 * @property string|null $cover_path
 * @property-read string|null $cover_url
 * @property-read string|null $duration_label
 */
class Recipe extends Model
{
    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'course',
        'portions',
        'duration_minutes',
        'diet',
        'steps',
        'cover_style',
        'cover_path',
    ];

    protected function casts(): array
    {
        return [
            'portions' => 'integer',
            'duration_minutes' => 'integer',
            'cover_style' => 'integer',
            'steps' => 'array',
        ];
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return HasMany<Ingredient, $this> */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class)->orderBy('position')->orderBy('id');
    }

    public function courseEnum(): Course
    {
        return Course::tryFrom($this->course) ?? Course::Snack;
    }

    /** @return Attribute<string|null, never> */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null);
    }

    /**
     * «35 Min.» / «1 Std. 20 Min.»
     *
     * @return Attribute<non-falsy-string|null, never>
     */
    protected function durationLabel(): Attribute
    {
        return Attribute::get(function (): ?string {
            $minutes = $this->duration_minutes;

            if (! $minutes) {
                return null;
            }

            if ($minutes < 60) {
                return "{$minutes} Min.";
            }

            $rest = $minutes % 60;

            return intdiv($minutes, 60).' Std.'.($rest ? " {$rest} Min." : '');
        });
    }
}
