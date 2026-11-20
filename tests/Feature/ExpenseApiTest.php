<?php

use App\Enums\ExpenseStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\JobTitle;
use App\Models\Level;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createApiEmployee(string $role = 'employee'): Employee
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

test('employee can list expense categories', function () {
    $employee = createApiEmployee();
    Sanctum::actingAs($employee);

    ExpenseCategory::factory()->create(['name' => 'Travel', 'is_active' => true]);
    ExpenseCategory::factory()->create(['name' => 'Inactive Cat', 'is_active' => false]);

    $response = $this->getJson('/api/v1/expenses/categories');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Travel');
});

test('employee can submit an expense and view own expenses', function () {
    $employee = createApiEmployee();
    Sanctum::actingAs($employee);

    $category = ExpenseCategory::factory()->create(['name' => 'Meals']);

    $payload = [
        'expense_category_id' => $category->id,
        'amount' => 85.50,
        'currency' => 'USD',
        'transaction_date' => now()->subDay()->toDateTimeString(),
        'description' => 'Client lunch meeting',
    ];

    $createResponse = $this->postJson('/api/v1/expenses', $payload);

    $createResponse->assertCreated()
        ->assertJsonPath('data.amount', 85.5)
        ->assertJsonPath('data.status', 'review')
        ->assertJsonPath('data.description', 'Client lunch meeting');

    $expenseId = $createResponse->json('data.id');

    $showResponse = $this->getJson("/api/v1/expenses/{$expenseId}");
    $showResponse->assertOk()
        ->assertJsonPath('data.id', $expenseId);

    $indexResponse = $this->getJson('/api/v1/expenses');
    $indexResponse->assertOk()
        ->assertJsonCount(1, 'data');
});

test('hr can review and approve or reject an expense', function () {
    $employee = createApiEmployee('employee');
    $hr = createApiEmployee('hr');

    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->create([
        'employee_id' => $employee->id,
        'expense_category_id' => $category->id,
        'status' => ExpenseStatus::Review,
    ]);

    Sanctum::actingAs($hr, ['access']);

    $approveResponse = $this->patchJson("/api/v1/admin/expenses/{$expense->id}/review", [
        'status' => 'approved',
    ]);

    $approveResponse->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.reviewed_by.id', $hr->id);

    expect($expense->fresh()->status)->toBe(ExpenseStatus::Approved);
});
