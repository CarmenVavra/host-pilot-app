<?php

namespace App\Models;

use App\Enums\Course;
use App\Enums\ShoppingCategory;
use App\Support\GermanDate;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * An event is the top level of the planning hierarchy. It owns exactly one
 * agenda (agenda items), a collection of recipes and one shopping list.
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property Carbon $date
 * @property string|null $time
 * @property string|null $location
 * @property int $guests
 * @property int $cover_style
 * @property string|null $cover_path
 * @property-read string $relative_label
 * @property-read string $short_date
 * @property-read string $medium_date
 * @property-read string $date_time_label
 * @property-read string|null $cover_url
 */
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /** Number of striped placeholder covers (see .cover--s{n} in app.css). */
    public const COVER_STYLES = 8;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'date',
        'time',
        'location',
        'guests',
        'cover_style',
        'cover_path',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'guests' => 'integer',
            'cover_style' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<AgendaItem, $this> */
    public function agendaItems(): HasMany
    {
        return $this->hasMany(AgendaItem::class)->orderBy('time')->orderBy('id');
    }

    /** @return HasMany<Recipe, $this> */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class)->orderBy('title');
    }

    /** @return HasMany<ShoppingItem, $this> */
    public function shoppingItems(): HasMany
    {
        return $this->hasMany(ShoppingItem::class)->orderBy('name');
    }

    /** @return HasMany<Person, $this> */
    public function people(): HasMany
    {
        return $this->hasMany(Person::class)->orderBy('id');
    }

    /**
     * @param  Builder<Event>  $query
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->whereDate('date', '>=', Carbon::today())->orderBy('date')->orderBy('time');
    }

    /**
     * @param  Builder<Event>  $query
     */
    public function scopePast(Builder $query): void
    {
        $query->whereDate('date', '<', Carbon::today())->orderByDesc('date');
    }

    public function isPast(): bool
    {
        return $this->date->lt(Carbon::today());
    }

    /**
     * «in 33 Tagen»
     *
     * @return Attribute<string, never>
     */
    protected function relativeLabel(): Attribute
    {
        return Attribute::get(fn () => GermanDate::relative($this->date));
    }

    /**
     * «So, 23. Nov»
     *
     * @return Attribute<string, never>
     */
    protected function shortDate(): Attribute
    {
        return Attribute::get(fn () => GermanDate::short($this->date));
    }

    /**
     * «So, 23. November»
     *
     * @return Attribute<string, never>
     */
    protected function mediumDate(): Attribute
    {
        return Attribute::get(fn () => GermanDate::medium($this->date));
    }

    /**
     * «So, 23. Nov · 10:00»
     *
     * @return Attribute<string, never>
     */
    protected function dateTimeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->short_date.($this->time ? ' · '.$this->time : ''));
    }

    /** @return Attribute<string|null, never> */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null);
    }

    /**
     * The agenda item highlighted on the timeline: the item in progress when
     * the event takes place today, otherwise the item at which the event starts.
     */
    public function highlightedAgendaItemId(): ?int
    {
        $items = $this->agendaItems;

        if ($items->isEmpty()) {
            return null;
        }

        if ($this->date->isToday()) {
            $now = Carbon::now()->format('H:i');

            $current = $items->filter(fn (AgendaItem $item) => $item->time <= $now)->last();

            return ($current ?? $items->first())->id;
        }

        return $items->firstWhere('time', $this->time)?->id;
    }

    /**
     * The next agenda item: the first one not yet started (today) or simply the first.
     */
    public function nextAgendaItem(): ?AgendaItem
    {
        $items = $this->agendaItems;

        if ($this->date->isToday()) {
            $now = Carbon::now()->format('H:i');

            return $items->first(fn (AgendaItem $item) => $item->time >= $now);
        }

        return $this->isPast() ? null : $items->first();
    }

    /**
     * Recipes grouped by course in the order of the day; empty courses are omitted.
     *
     * @return Collection<array-key, EloquentCollection<int, Recipe>>
     */
    public function recipesByCourse(): Collection
    {
        return $this->recipes
            ->sortBy(fn (Recipe $recipe) => sprintf('%02d %s', $recipe->courseEnum()->order(), Str::lower(Str::ascii($recipe->title))))
            ->groupBy(fn (Recipe $recipe) => $recipe->courseEnum()->value);
    }

    /**
     * Shopping items grouped by supermarket section in a fixed order.
     *
     * @return Collection<array-key, EloquentCollection<int, ShoppingItem>>
     */
    public function shoppingByCategory(): Collection
    {
        return $this->shoppingItems
            ->sortBy(fn (ShoppingItem $item) => sprintf(
                '%02d %s',
                ShoppingCategory::tryFrom($item->category)?->order() ?? 99,
                Str::lower(Str::ascii($item->name)),
            ))
            ->groupBy('category');
    }

    /**
     * «Frühstück bis Nachspeise» – the range of courses covered by the recipes.
     */
    public function courseRangeLabel(): ?string
    {
        $courses = $this->recipes
            ->map(fn (Recipe $recipe) => $recipe->courseEnum())
            ->unique()
            ->sortBy(fn (Course $course) => $course->order())
            ->values();

        if ($courses->isEmpty()) {
            return null;
        }

        if ($courses->count() === 1) {
            return $courses->first()->value;
        }

        return $courses->first()->value.' bis '.$courses->last()->value;
    }
}
