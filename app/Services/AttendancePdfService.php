<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Support\SimplePdf;
use App\Support\WorkDuration;

class AttendancePdfService
{
    /** Builds the one-day attendance report and returns the PDF binary. */
    public function render(AttendanceRecord $record, Employee $employee, CompanySetting $settings): string
    {
        $tz = $settings->timezone;

        $format = fn ($date, string $pattern = 'H:i') => $date ? $date->copy()->setTimezone($tz)->format($pattern) : '-';

        $pdf = (new SimplePdf())
            ->line('Attendance Report', 20, true)
            ->line((string) $settings->name, 12)
            ->space(14)
            ->line('Employee: ' . $employee->full_name . '  (' . $employee->employee_id . ')')
            ->line('Date: ' . $record->work_date->toDateString())
            ->line('Clock in: ' . $format($record->clock_in_at))
            ->line('Clock out: ' . $format($record->clock_out_at))
            ->line('Total hours: ' . $this->hours(WorkDuration::calculate($record)), 12, true)
            ->space(12)
            ->line('Breaks (' . $record->breaks->count() . ')', 13, true);

        foreach ($record->breaks->sortBy('started_at')->values() as $index => $break) {
            $minutes = $break->ended_at ? (int) round($break->started_at->diffInSeconds($break->ended_at, false) / 60) : null;

            $pdf->line(sprintf(
                '%d. %s - %s%s',
                $index + 1,
                $format($break->started_at),
                $format($break->ended_at),
                $minutes === null ? '' : "  ({$minutes} min)"
            ));
        }

        $pdf->space(12)
            ->line('Notes: ' . ($record->notes ?: '-'))
            ->line(sprintf('Location: %.6f, %.6f', $record->clock_in_latitude, $record->clock_in_longitude))
            ->space(12)
            ->line('Times are shown in ' . $tz . '.', 9);

        return $pdf->output();
    }

    private function hours(int $seconds): string
    {
        return sprintf('%dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
    }
}
