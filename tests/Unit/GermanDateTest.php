<?php

namespace Tests\Unit;

use App\Support\GermanDate;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class GermanDateTest extends TestCase
{
    public function test_formats_swiss_german_dates(): void
    {
        $date = Carbon::create(2026, 11, 23);

        $this->assertSame('Mo, 23. Nov', GermanDate::short($date));
        $this->assertSame('Mo, 23. November', GermanDate::medium($date));
        $this->assertSame('Montag, 23. November 2026', GermanDate::long($date));
    }

    public function test_relative_labels(): void
    {
        $today = Carbon::create(2026, 10, 21);

        $this->assertSame('in 33 Tagen', GermanDate::relative(Carbon::create(2026, 11, 23), $today));
        $this->assertSame('morgen', GermanDate::relative(Carbon::create(2026, 10, 22), $today));
        $this->assertSame('heute', GermanDate::relative(Carbon::create(2026, 10, 21, 18), $today));
        $this->assertSame('gestern', GermanDate::relative(Carbon::create(2026, 10, 20), $today));
        $this->assertSame('vor 5 Tagen', GermanDate::relative(Carbon::create(2026, 10, 16), $today));
    }
}
