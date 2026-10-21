<?php

namespace App\Models;

use App\Support\Quantity;
use Database\Factories\IngredientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $recipe_id
 * @property string $name
 * @property float|null $amount
 * @property string $unit
 * @property string $category
 * @property int $position
 */
class Ingredient extends Model
{
    /** @use HasFactory<IngredientFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'amount', 'unit', 'category', 'position'];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * The formatted amount, scaled from the recipe's portions to the given number of guests.
     */
    public function scaledLabel(int $portions, int $guests): string
    {
        return Quantity::format(Quantity::scale($this->amount, $portions, $guests), $this->unit);
    }
}
