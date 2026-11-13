<?php

namespace App\Support;

use Carbon\CarbonInterface;

class IsoDate
{
    /** ISO 8601 UTC string (e.g. 2026-11-09T09:00:00Z), or null. Matches the contract's DateTime format. */
    public static function utc(?CarbonInterface $date): ?string
    {
        return $date?->copy()->utc()->toIso8601ZString();
    }
}
