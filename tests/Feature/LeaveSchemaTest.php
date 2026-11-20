<?php

use App\Enums\LeaveStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\JobTitle;
use App\Models\LeaveCategory;
use App\Models\LeaveRequest;
use App\Models\Level;
use Database\Seeders\LeaveCategorySeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createLeaveSchemaEmployee(): Employee
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

test('leave category seeder creates default categories idempotently', function () {
    $this->seed(LeaveCategorySeeder::class);
    $this->seed(LeaveCategorySeeder::class);

    expect(LeaveCategory::count())->toBe(4)
        ->and(LeaveCategory::where('name', 'Annual')->value('default_days'))->toBe(21.0)
        ->and(LeaveCategory::where('name', 'Unpaid')->value('requires_balance'))->toBeFalse();
});

test('employee leave balance enforces unique employee category year and computes remaining days', function () {
    $employee = createLeaveSchemaEmployee();
    $category = LeaveCategory::factory()->create(['default_days' => 21]);

    $balance = EmployeeLeaveBalance::factory()->create([
        'employee_id' => $employee->id,
        'leave_category_id' => $category->id,
        'year' => 2026,
        'total_days' => 21,
        'used_days' => 5,
    ]);

    expect($balance->remaining_days)->toBe(16.0);

    expect(fn () => EmployeeLeaveBalance::factory()->create([
        'employee_id' => $employee->id,
        'leave_category_id' => $category->id,
        'year' => 2026,
        'total_days' => 21,
        'used_days' => 0,
    ]))->toThrow(QueryException::class);
});

test('leave request defaults to review status and resolves employee relationships', function () {
    $employee = createLeaveSchemaEmployee();
    $delegate = createLeaveSchemaEmployee();
    $reviewer = createLeaveSchemaEmployee();
    $category = LeaveCategory::factory()->create(['name' => 'Annual']);

    $leaveRequest = LeaveRequest::factory()->create([
        'employee_id' => $employee->id,
        'leave_category_id' => $category->id,
        'task_delegate_id' => $delegate->id,
        'days_count' => 4,
    ]);

    expect($leaveRequest->status)->toBe(LeaveStatus::Review)
        ->and($leaveRequest->days_count)->toBe(4.0);

    $leaveRequest->update([
        'status' => LeaveStatus::Approved,
        'reviewed_by_id' => $reviewer->id,
        'reviewed_at' => now(),
    ]);

    $leaveRequest->load(['employee', 'leaveCategory', 'taskDelegate', 'reviewedBy']);

    expect($leaveRequest->employee->id)->toBe($employee->id)
        ->and($leaveRequest->leaveCategory->id)->toBe($category->id)
        ->and($leaveRequest->taskDelegate->id)->toBe($delegate->id)
        ->and($leaveRequest->reviewedBy->id)->toBe($reviewer->id)
        ->and($employee->leaveRequests()->whereKey($leaveRequest->id)->exists())->toBeTrue()
        ->and($delegate->delegatedLeaveRequests()->whereKey($leaveRequest->id)->exists())->toBeTrue()
        ->and($reviewer->reviewedLeaveRequests()->whereKey($leaveRequest->id)->exists())->toBeTrue();
});
