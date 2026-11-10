<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\PayrollRecord;
use Illuminate\Database\Seeder;

/** Demo data: the last 6 closed months for each active employee. Safe to run twice. */
class PayrollRecordSeeder extends Seeder
{
    public function run(): void
    {
        Employee::query()->where('is_active', true)->each(function (Employee $employee) {
            foreach (range(1, 6) as $monthsAgo) {
                $month = now()->subMonthsNoOverflow($monthsAgo)->startOfMonth();

                if (PayrollRecord::where('employee_id', $employee->id)
                    ->whereDate('period_start', $month->toDateString())->exists()) {
                    continue;
                }

                PayrollRecord::factory()->forMonth($month)->create(['employee_id' => $employee->id]);
            }
        });
    }
}
