<?php

namespace Database\Factories;

use App\Models\AgendaItem;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaItem>
 */
class AgendaItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'person_id' => null,
            'time' => sprintf('%02d:%02d', fake()->numberBetween(8, 22), fake()->randomElement([0, 30])),
            'title' => fake()->randomElement(['Tisch decken', 'Gäste empfangen', 'Dessert servieren', 'Aufräumen']),
            'note' => null,
        ];
    }
}
