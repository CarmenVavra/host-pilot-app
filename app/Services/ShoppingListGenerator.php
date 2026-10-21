<?php

namespace App\Services;

use App\Models\Event;
use App\Models\ShoppingItem;
use App\Support\Quantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds an event's shopping list from the ingredients of its recipes.
 *
 * - Amounts are taken unchanged from the recipes (no scaling to guests).
 * - Equal ingredients (same name and base unit) are summed up.
 * - Manually added entries and existing check marks are preserved.
 * - Recipe entries that no longer occur in any recipe are removed unless
 *   they were already checked off.
 */
class ShoppingListGenerator
{
    public function generate(Event $event): ShoppingListGenerationResult
    {
        $event->load('recipes.ingredients');

        /** @var array<string, array{name: string, amount: float|null, unit: string, category: string}> $totals */
        $totals = [];

        foreach ($event->recipes as $recipe) {
            foreach ($recipe->ingredients as $ingredient) {
                [$amount, $unit] = Quantity::normalize($ingredient->amount, $ingredient->unit);
                $key = self::key($ingredient->name, $unit);

                if (isset($totals[$key])) {
                    $totals[$key]['amount'] = $amount === null
                        ? $totals[$key]['amount']
                        : ($totals[$key]['amount'] ?? 0) + $amount;

                    continue;
                }

                $totals[$key] = [
                    'name' => trim($ingredient->name),
                    'amount' => $amount,
                    'unit' => $unit,
                    'category' => $ingredient->category,
                ];
            }
        }

        DB::transaction(function () use ($event, $totals) {
            $existing = $event->shoppingItems()
                ->where('source', ShoppingItem::SOURCE_RECIPE)
                ->get()
                ->keyBy(fn (ShoppingItem $item) => self::key($item->name, (string) $item->unit));

            foreach ($totals as $key => $data) {
                $item = $existing->get($key);

                if ($item) {
                    $item->update([
                        'amount' => $data['amount'],
                        'category' => $data['category'],
                    ]);

                    continue;
                }

                $event->shoppingItems()->create($data + [
                    'done' => false,
                    'source' => ShoppingItem::SOURCE_RECIPE,
                ]);
            }

            $existing
                ->reject(fn (ShoppingItem $item, string $key) => isset($totals[$key]) || $item->done)
                ->each(fn (ShoppingItem $item) => $item->delete());
        });

        return new ShoppingListGenerationResult(
            ingredients: count($totals),
            recipes: $event->recipes->count(),
        );
    }

    private static function key(string $name, string $unit): string
    {
        return Str::lower(trim($name)).'|'.$unit;
    }
}
