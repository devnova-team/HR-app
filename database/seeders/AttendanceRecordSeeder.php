<?php

namespace Database\Seeders;

use App\Enums\ClockStatus;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class AttendanceRecordSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::first();

        if (!$employee) {
            return;
        }

        $schedule = WorkSchedule::where('is_default', true)->first();

        for ($i = 7; $i >= 1; $i--) {
            $date = now()->subDays($i)->toDateString();

            $record = AttendanceRecord::create([
                'employee_id' => $employee->id,
                'work_schedule_id' => $schedule?->id,
                'work_date' => $date,
                'clock_in_at' => $date . ' 09:00:00',
                'clock_out_at' => $date . ' 17:00:00',
                'selfie_url' => 'attendance/selfies/test-selfie.jpg',
                'clock_in_latitude' => 30.0444000,
                'clock_in_longitude' => 31.2357000,
                'notes' => 'Seeded test record',
                'status' => ClockStatus::CLOCKED_OUT,
            ]);

            if ($i % 2 !== 0) {
                $record->breaks()->create([
                    'started_at' => $date . ' 12:00:00',
                    'ended_at' => $date . ' 13:00:00',
                ]);

                if ($i === 5) {
                    $record->breaks()->create([
                        'started_at' => $date . ' 15:30:00',
                        'ended_at' => $date . ' 15:45:00',
                    ]);
                }
            }
        }
    }
}
