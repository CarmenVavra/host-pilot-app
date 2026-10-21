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
     * The formatted amount as entered in the recipe («1.6 kg», «4 Stk»).
     */
    public function quantityLabel(): string
    {
        return Quantity::format($this->amount, $this->unit);
    }
}
