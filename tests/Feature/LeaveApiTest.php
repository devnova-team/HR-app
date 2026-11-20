<?php

use App\Enums\LeaveStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\JobTitle;
use App\Models\LeaveCategory;
use App\Models\LeaveRequest;
use App\Models\Level;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createLeaveApiEmployee(string $role = 'employee'): Employee
{
    $department = Department::query()->firstOrCreate(['name' => 'IT']);
    $level = Level::query()->firstOrCreate(['rank' => 3], ['name' => 'Senior']);
    $jobTitle = JobTitle::query()->firstOrCreate([
        'department_id' => $department->id,
        'name' => 'Engineer',
    ]);

    $employee = Employee::factory()->create([
        'department_id' => $department->id,
        'level_id' => $level->id,
        'job_title_id' => $jobTitle->id,
    ]);

    if ($role === 'hr') {
        test()->seed(RolesAndPermissionsSeeder::class);
        $employee->assignRole('hr');
    }

    return $employee;
}

test('employee can list leave categories and balances', function () {
    $employee = createLeaveApiEmployee();
    Sanctum::actingAs($employee);

    $category = LeaveCategory::factory()->create(['name' => 'Annual', 'is_active' => true]);
    EmployeeLeaveBalance::factory()->create([
        'employee_id' => $employee->id,
        'leave_category_id' => $category->id,
        'year' => (int) now()->format('Y'),
        'total_days' => 21,
        'used_days' => 5,
    ]);

    $categoriesResponse = $this->getJson('/api/v1/leaves/categories');
    $categoriesResponse->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Annual');

    $balancesResponse = $this->getJson('/api/v1/leaves/balances');
    $balancesResponse->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.remaining_days', 16);
});

test('employee can submit a leave request with balance validation', function () {
    $employee = createLeaveApiEmployee();
    $delegate = createLeaveApiEmployee();
    Sanctum::actingAs($employee);

    $category = LeaveCategory::factory()->create([
        'name' => 'Annual',
        'requires_balance' => true,
    ]);

    EmployeeLeaveBalance::factory()->create([
        'employee_id' => $employee->id,
        'leave_category_id' => $category->id,
        'year' => 2026,
        'total_days' => 10,
        'used_days' => 8,
    ]);

    // Requesting 5 days when only 2 remain should fail
    $failPayload = [
        'leave_category_id' => $category->id,
        'task_delegate_id' => $delegate->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-05',
        'days_count' => 5,
        'description' => 'Summer vacation',
        'emergency_contact_name' => 'Ahmad',
        'emergency_contact_phone' => '01012345678',
    ];

    $failResponse = $this->postJson('/api/v1/leaves', $failPayload);
    $failResponse->assertStatus(422);

    // Requesting 2 days should succeed
    $successPayload = array_merge($failPayload, [
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-02',
        'days_count' => 2,
    ]);

    $successResponse = $this->postJson('/api/v1/leaves', $successPayload);
    $successResponse->assertCreated()
        ->assertJsonPath('data.status', 'review')
        ->assertJsonPath('data.days_count', 2);

    $leaveId = $successResponse->json('data.id');

    $showResponse = $this->getJson("/api/v1/leaves/{$leaveId}");
    $showResponse->assertOk()
        ->assertJsonPath('data.id', $leaveId);
});

test('hr can review and approve a leave request which deducts the balance', function () {
    $employee = createLeaveApiEmployee('employee');
    $hr = createLeaveApiEmployee('hr');

    $category = LeaveCategory::factory()->create([
        'name' => 'Annual',
        'requires_balance' => true,
    ]);

    $balance = EmployeeLeaveBalance::factory()->create([
        'employee_id' => $employee->id,
        'leave_category_id' => $category->id,
        'year' => 2026,
        'total_days' => 20,
        'used_days' => 2,
    ]);

    $leave = LeaveRequest::factory()->create([
        'employee_id' => $employee->id,
        'leave_category_id' => $category->id,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-03',
        'days_count' => 3,
        'status' => LeaveStatus::Review,
    ]);

    Sanctum::actingAs($hr, ['access']);

    $reviewResponse = $this->patchJson("/api/v1/admin/leaves/{$leave->id}/review", [
        'status' => 'approved',
    ]);

    $reviewResponse->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.reviewed_by.id', $hr->id);

    expect($leave->fresh()->status)->toBe(LeaveStatus::Approved)
        ->and((float) $balance->fresh()->used_days)->toBe(5.0)
        ->and((float) $balance->fresh()->remaining_days)->toBe(15.0);
});
