<?php

namespace App\Enums;

/**
 * Courses of a menu, declared in the order they occur during the day.
 */
enum Course: string
{
    case Breakfast = 'Frühstück';
    case Aperitif = 'Aperitif';
    case Brunch = 'Brunch';
    case Starter = 'Vorspeise';
    case Lunch = 'Mittag';
    case Main = 'Hauptgang';
    case Side = 'Beilage';
    case Dinner = 'Abend';
    case Dessert = 'Nachspeise';
    case Snack = 'Snack';

    /**
     * Position of the course within the day (0 = earliest).
     */
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
