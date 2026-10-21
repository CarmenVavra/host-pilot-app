<?php

namespace Database\Factories;

use App\Enums\Course;
use App\Models\Event;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'title' => fake()->randomElement(['Shakshuka', 'Risotto', 'Zitronen-Tarte', 'Gurkensalat']),
            'course' => fake()->randomElement(Course::values()),
            'portions' => 4,
            'duration_minutes' => 30,
            'diet' => 'vegetarisch',
            'steps' => ['Vorbereiten.', 'Kochen.', 'Servieren.'],
            'cover_style' => 0,
        ];
    }
}
