<?php

namespace App\Enums;

/**
 * Units for ingredient and shopping quantities.
 */
enum Unit: string
{
    case Piece = 'Stk';
    case Gram = 'g';
    case Kilogram = 'kg';
    case Millilitre = 'ml';
    case Litre = 'l';
    case Teaspoon = 'TL';
    case Tablespoon = 'EL';
    case Bunch = 'Bund';
    case Bottle = 'Fl.';
    case Pack = 'Pck.';
    case Can = 'Dose';
    case Pinch = 'Prise';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
