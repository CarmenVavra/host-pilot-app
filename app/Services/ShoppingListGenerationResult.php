<?php

namespace App\Services;

/**
 * Summary of a shopping list generation, used for the confirmation toast.
 */
final readonly class ShoppingListGenerationResult
{
    public function __construct(
        public int $ingredients,
        public int $recipes,
    ) {}

    /** «32 Zutaten aus 5 Rezepten» */
    public function message(): string
    {
        $ingredients = $this->ingredients === 1 ? '1 Zutat' : "{$this->ingredients} Zutaten";
        $recipes = $this->recipes === 1 ? '1 Rezept' : "{$this->recipes} Rezepten";

        return "{$ingredients} aus {$recipes}";
    }
}
