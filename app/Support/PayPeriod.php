<?php

namespace App\Support;

use Carbon\Carbon;

class PayPeriod
{
   
    public static function getDates($date = null, int $startDay = 1, ?string $timezone = null): array
    {
        $currentDate = $date
            ? Carbon::parse($date, $timezone)
            : now($timezone);

        $tz = $currentDate->getTimezone();

        if ($startDay <= 1) {
            return [
                'start' => $currentDate->copy()->startOfMonth(),
                'end'   => $currentDate->copy()->endOfMonth(),
            ];
        }

        
        $monthStart = $currentDate->copy()->startOfMonth();

        $effectiveStartDay = min($startDay, $currentDate->daysInMonth);

        if ($currentDate->day >= $effectiveStartDay) {
            $start = self::createValidDate($monthStart->year, $monthStart->month, $startDay, $tz);

            $next = $monthStart->copy()->addMonth();
            $end = self::createValidDate($next->year, $next->month, $startDay, $tz)
                ->subDay()
                ->endOfDay();
        } else {
            $prev = $monthStart->copy()->subMonth();
            $start = self::createValidDate($prev->year, $prev->month, $startDay, $tz);

            $end = self::createValidDate($monthStart->year, $monthStart->month, $startDay, $tz)
                ->subDay()
                ->endOfDay();
        }

        return [
            'start' => $start->startOfDay(),
            'end'   => $end,
        ];
    }

    private static function createValidDate(int $year, int $month, int $day, $tz): Carbon
    {
        $daysInMonth = Carbon::create($year, $month, 1, 0, 0, 0, $tz)->daysInMonth;

        return Carbon::create($year, $month, min($day, $daysInMonth), 0, 0, 0, $tz);
    }
}