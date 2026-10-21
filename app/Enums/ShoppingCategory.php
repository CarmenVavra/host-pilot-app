<?php

namespace App\Enums;

/**
 * Supermarket sections used to group the shopping list.
 */
enum ShoppingCategory: string
{
    case Produce = 'Obst & Gemüse';
    case Bakery = 'Brot & Backwaren';
    case Dairy = 'Milch & Eier';
    case MeatFish = 'Fleisch & Fisch';
    case Pantry = 'Gewürze & Vorrat';
    case Frozen = 'Tiefkühl';
    case Drinks = 'Getränke';
    case Other = 'Sonstiges';

    public function order(): int
    {
        return (int) array_search($this, self::cases(), true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
