<?php

namespace Tests\Unit;

use App\Support\Quantity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QuantityTest extends TestCase
{
    /**
     * @return array<string, array{0: float|null, 1: string|null, 2: string}>
     */
    public static function formats(): array
    {
        return [
            'pieces round up' => [4.2, 'Stk', '5 Stk'],
            'whole pieces' => [12.0, 'Stk', '12 Stk'],
            'teaspoons round up' => [3.34, 'TL', '4 TL'],
            'grams round to 10' => [133.0, 'g', '130 g'],
            'grams minimum 10' => [2.0, 'g', '10 g'],
            'grams to kilograms' => [1600.0, 'g', '1.6 kg'],
            'kilograms stay kilograms' => [1.25, 'kg', '1.3 kg'],
            'whole kilograms' => [2000.0, 'g', '2 kg'],
            'millilitres round' => [333.0, 'ml', '330 ml'],
            'millilitres to litres' => [1500.0, 'ml', '1.5 l'],
            'litres input' => [2.0, 'l', '2 l'],
            'bottles' => [5.5, 'Fl.', '6 Fl.'],
            'no amount' => [null, 'Stk', ''],
        ];
    }

    #[DataProvider('formats')]
    public function test_formats_quantities_with_design_rounding_rules(?float $amount, ?string $unit, string $expected): void
    {
        $this->assertSame($expected, Quantity::format($amount, $unit));
    }

    public function test_scales_from_portions_to_guests(): void
    {
        $this->assertSame(24.0, Quantity::scale(12, 6, 12));
        $this->assertSame(3.0, Quantity::scale(4, 4, 3));
        $this->assertNull(Quantity::scale(null, 4, 8));
    }

    public function test_normalizes_large_units(): void
    {
        $this->assertSame([1500.0, 'g'], Quantity::normalize(1.5, 'kg'));
        $this->assertSame([2000.0, 'ml'], Quantity::normalize(2, 'l'));
        $this->assertSame([3.0, 'Stk'], Quantity::normalize(3, 'Stk'));
    }
}
