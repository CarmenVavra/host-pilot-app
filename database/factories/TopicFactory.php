<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Topic>
 */
class TopicFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'title' => fake()->randomElement(['Sommerferien planen', 'Geschenk für Lena', 'Nächstes Treffen']),
            'notes' => null,
        ];
    }
}
