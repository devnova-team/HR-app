<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\JobTitle;
use App\Models\LeaveCategory;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeLeaveBalance>
 */
class EmployeeLeaveBalanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => fn () => $this->createDefaultEmployee()->id,
            'leave_category_id' => LeaveCategory::factory(),
            'year' => now()->year,
            'total_days' => 21,
            'used_days' => 0,
        ];
    }

    private function createDefaultEmployee(): Employee
    {
        $department = Department::query()->firstOrCreate(['name' => 'IT']);
        $level = Level::query()->firstOrCreate(['rank' => 2], ['name' => 'Junior']);
        $jobTitle = JobTitle::query()->firstOrCreate([
            'department_id' => $department->id,
            'name' => 'Back-End Developer',
        ]);

        return Employee::factory()->create([
            'department_id' => $department->id,
            'level_id' => $level->id,
            'job_title_id' => $jobTitle->id,
        ]);
    }
}
