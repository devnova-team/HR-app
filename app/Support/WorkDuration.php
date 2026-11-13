<?php

namespace App\Support;


use Carbon\CarbonInterface;

class WorkDuration
{

    public static function calculate($record, ?CarbonInterface $now = null): int
    {
        if (!$record || !$record->clock_in_at) {
            return 0;
        }

        $now = $now ?? now();

        $endTime = $record->clock_out_at ? $record->clock_out_at->copy() : $now->copy();

        $totalSeconds = $record->clock_in_at->diffInSeconds($endTime, false);

        if ($totalSeconds <= 0) {
            return 0;
        }

        $totalBreakSeconds = 0;

        if (!empty($record->breaks)) {
            foreach ($record->breaks as $break) {
                if (!$break->started_at) {
                    continue;
                }

                $breakEnd = $break->ended_at ? $break->ended_at->copy() : $endTime->copy();
                $breakSeconds = $break->started_at->diffInSeconds($breakEnd, false);

                if ($breakSeconds > 0) {
                    $totalBreakSeconds += $breakSeconds;
                }
            }
        }

        $netSeconds = $totalSeconds - $totalBreakSeconds;

        return max(0, (int) $netSeconds);
    }
}
