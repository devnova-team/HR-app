<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;


class ApiDate
{
    private const FORMAT = 'Y-m-d\TH:i:s\Z';

    /** For timestamp columns (real instants). */
    public static function iso(CarbonInterface|string|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->utc()->format(self::FORMAT);
    }

    /** For date-only columns: keeps the calendar day, midnight UTC. */
    public static function isoDate(CarbonInterface|string|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->startOfDay()->shiftTimezone('UTC')->format(self::FORMAT);
    }
}
