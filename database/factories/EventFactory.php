<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Raclette-Abend', 'Sommerfest', 'Geburtstags-Brunch', 'Grillparty']),
            'date' => fake()->dateTimeBetween('+1 day', '+60 days')->format('Y-m-d'),
            'time' => '18:00',
            'location' => fake()->city(),
            'guests' => 12,
            'cover_style' => fake()->numberBetween(0, Event::COVER_STYLES - 1),
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'date' => fake()->dateTimeBetween('-60 days', '-1 day')->format('Y-m-d'),
        ]);
    }
}
