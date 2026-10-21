<?php

namespace App\Models;

use App\Support\Quantity;
use Database\Factories\ShoppingItemFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An entry of an event's shopping list – generated from recipes or added manually.
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property float|null $amount
 * @property string|null $unit
 * @property string $category
 * @property bool $done
 * @property string $source
 * @property-read string $quantity_label
 */
class ShoppingItem extends Model
{
    /** @use HasFactory<ShoppingItemFactory> */
    use HasFactory;

    public const SOURCE_RECIPE = 'recipe';

    public const SOURCE_MANUAL = 'manual';

    /** @var list<string> */
    protected $fillable = ['name', 'amount', 'unit', 'category', 'done', 'source'];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'done' => 'boolean',
        ];
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isManual(): bool
    {
        return $this->source === self::SOURCE_MANUAL;
    }

    /** @return Attribute<string, never> */
    protected function quantityLabel(): Attribute
    {
        return Attribute::get(fn () => Quantity::format($this->amount, $this->unit));
    }
}
