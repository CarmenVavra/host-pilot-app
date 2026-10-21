<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Swiss German date labels as used in the HostPilot design («So, 23. Nov», «in 33 Tagen»).
 */
final class GermanDate
{
    private const WEEKDAYS_SHORT = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];

    private const WEEKDAYS_LONG = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];

    private const MONTHS_SHORT = ['Jan', 'Feb', 'März', 'Apr', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez'];

    private const MONTHS_LONG = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

    /** «So, 23. Nov» */
    public static function short(CarbonInterface $date): string
    {
        return self::WEEKDAYS_SHORT[$date->dayOfWeek].', '.$date->day.'. '.self::MONTHS_SHORT[$date->month - 1];
    }

    /** «So, 23. November» */
    public static function medium(CarbonInterface $date): string
    {
        return self::WEEKDAYS_SHORT[$date->dayOfWeek].', '.$date->day.'. '.self::MONTHS_LONG[$date->month - 1];
    }

    /** «Sonntag, 23. November 2026» */
    public static function long(CarbonInterface $date): string
    {
        return self::WEEKDAYS_LONG[$date->dayOfWeek].', '.$date->day.'. '.self::MONTHS_LONG[$date->month - 1].' '.$date->year;
    }

    /** «in 33 Tagen», «morgen», «heute», «vor 3 Tagen» */
    public static function relative(CarbonInterface $date, ?CarbonInterface $today = null): string
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $days = (int) $today->diffInDays($date->copy()->startOfDay(), false);

        return match (true) {
            $days === 0 => 'heute',
            $days === 1 => 'morgen',
            $days === -1 => 'gestern',
            $days > 1 => "in {$days} Tagen",
            default => 'vor '.abs($days).' Tagen',
        };
    }
}
