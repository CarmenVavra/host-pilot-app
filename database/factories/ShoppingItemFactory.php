<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\ShoppingItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShoppingItem>
 */
class ShoppingItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->randomElement(['Servietten', 'Kerzen', 'Eiswürfel']),
            'amount' => 1,
            'unit' => 'Pck.',
            'category' => 'Sonstiges',
            'done' => false,
            'source' => ShoppingItem::SOURCE_MANUAL,
        ];
    }
}
