<?php

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\LeaveCategory;
use App\Models\LeaveRequest;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = now()->addDays(2)->startOfDay();
        $endDate = (clone $startDate)->addDays(2)->endOfDay();

        return [
            'employee_id' => fn () => $this->createDefaultEmployee()->id,
            'leave_category_id' => LeaveCategory::factory(),
            'task_delegate_id' => null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_count' => 3,
            'description' => fake()->sentence(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->numerify('01#########'),
            'status' => LeaveStatus::Review,
            'reviewed_by_id' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function approved(?Employee $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaveStatus::Approved,
            'reviewed_by_id' => $reviewer?->id ?? $this->createDefaultEmployee()->id,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(?Employee $reviewer = null, string $reason = 'Insufficient coverage'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaveStatus::Rejected,
            'reviewed_by_id' => $reviewer?->id ?? $this->createDefaultEmployee()->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
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
