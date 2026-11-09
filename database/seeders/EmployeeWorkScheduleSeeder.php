<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeWorkSchedule;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class EmployeeWorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::take(3)->get();

        if ($employees->isEmpty()) {
            return;
        }

        $default = WorkSchedule::where('is_default', true)->first();
        $evening = WorkSchedule::where('is_default', false)->first();

       
        if (isset($employees[0]) && $evening) {
            EmployeeWorkSchedule::updateOrCreate(
                ['employee_id' => $employees[0]->id, 'effective_from' => now()->subDays(30)->toDateString()],
                ['work_schedule_id' => $evening->id, 'effective_to' => null]
            );
        }

        if (isset($employees[1]) && $default) {
            EmployeeWorkSchedule::updateOrCreate(
                ['employee_id' => $employees[1]->id, 'effective_from' => now()->subDays(15)->toDateString()],
                ['work_schedule_id' => $default->id, 'effective_to' => null]
            );
        }
    }
}