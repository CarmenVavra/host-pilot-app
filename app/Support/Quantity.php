<?php

namespace App\Support;

/**
 * Normalises and formats ingredient quantities.
 *
 * Rounding rules (from the design handoff):
 *  - g is rounded to 10 g (minimum 10 g); from 1000 g it is shown as kg with one decimal.
 *  - ml is handled analogously and shown as l from 1000 ml.
 *  - Every other unit (Stk, Bund, Fl., TL, …) is rounded up to a whole number.
 */
final class Quantity
{
    /**
     * Converts large units to their base unit so equal ingredients can be summed.
     *
     * @return array{0: float|null, 1: string}
     */
    public static function normalize(?float $amount, string $unit): array
    {
        return match ($unit) {
            'kg' => [$amount === null ? null : $amount * 1000, 'g'],
            'l' => [$amount === null ? null : $amount * 1000, 'ml'],
            default => [$amount, $unit],
        };
    }

    /**
     * Scales an amount from the recipe's portions to the number of guests.
     */
    public static function scale(?float $amount, int $portions, int $guests): ?float
    {
        if ($amount === null) {
            return null;
        }

        return $amount * $guests / max(1, $portions);
    }

    public static function format(?float $amount, ?string $unit): string
    {
        if ($amount === null || $amount <= 0) {
            return '';
        }

        [$value, $unit] = self::normalize($amount, (string) $unit);

        return match ($unit) {
            'g' => self::metric($value, 'g', 'kg', 10),
            'ml' => self::metric($value, 'ml', 'l', 10),
            '' => self::number(ceil($value - 1e-9)),
            default => self::number(ceil($value - 1e-9)).' '.$unit,
        };
    }

    private static function metric(float $value, string $small, string $large, int $minimum): string
    {
        if ($value >= 1000) {
            return self::number(round($value / 1000, 1)).' '.$large;
        }

        return self::number(max($minimum, round($value / 10) * 10)).' '.$small;
    }

    private static function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', "'"), '0'), '.');
    }
}
