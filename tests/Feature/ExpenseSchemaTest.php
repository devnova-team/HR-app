<?php

use App\Enums\ExpenseStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\JobTitle;
use App\Models\Level;
use App\Models\PayrollRecord;
use Database\Seeders\ExpenseCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createExpenseSchemaEmployee(): Employee
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

test('expense category seeder creates default categories idempotently', function () {
    $this->seed(ExpenseCategorySeeder::class);
    $this->seed(ExpenseCategorySeeder::class);

    expect(ExpenseCategory::count())->toBe(6)
        ->and(ExpenseCategory::where('name', 'Travel')->exists())->toBeTrue()
        ->and(ExpenseCategory::where('name', 'Transportation')->value('is_active'))->toBeTrue();
});

test('expense defaults to review status and casts attributes correctly', function () {
    $employee = createExpenseSchemaEmployee();
    $category = ExpenseCategory::factory()->create(['name' => 'Travel']);

    $expense = Expense::factory()->create([
        'employee_id' => $employee->id,
        'expense_category_id' => $category->id,
        'amount' => 150.75,
        'currency' => 'USD',
    ]);

    expect($expense->status)->toBe(ExpenseStatus::Review)
        ->and($expense->amount)->toBe('150.75')
        ->and($expense->currency)->toBe('USD')
        ->and($expense->category_id)->toBe($category->id);
});

test('expense resolves all relationships including reviewer and payroll record', function () {
    $employee = createExpenseSchemaEmployee();
    $reviewer = createExpenseSchemaEmployee();
    $category = ExpenseCategory::factory()->create(['name' => 'Office Supplies']);

    $payrollRecord = PayrollRecord::create([
        'employee_id' => $employee->id,
        'period_start' => now()->startOfMonth()->toDateString(),
        'period_end' => now()->endOfMonth()->toDateString(),
        'total_hours_minutes' => 160,
        'basic_salary' => 5000,
        'reimbursement' => 250,
        'total_salary' => 5250,
        'currency' => 'USD',
        'paid_on' => now(),
    ]);

    $expense = Expense::factory()->approved()->create([
        'employee_id' => $employee->id,
        'expense_category_id' => $category->id,
        'reviewed_by_id' => $reviewer->id,
        'payroll_record_id' => $payrollRecord->id,
        'amount' => 250.00,
    ]);

    $expense->load(['employee', 'expenseCategory', 'category', 'reviewedBy', 'payrollRecord']);

    expect($expense->employee->id)->toBe($employee->id)
        ->and($expense->expenseCategory->id)->toBe($category->id)
        ->and($expense->category->id)->toBe($category->id)
        ->and($expense->reviewedBy->id)->toBe($reviewer->id)
        ->and($expense->payrollRecord->id)->toBe($payrollRecord->id)
        ->and($employee->expenses()->whereKey($expense->id)->exists())->toBeTrue()
        ->and($reviewer->reviewedExpenses()->whereKey($expense->id)->exists())->toBeTrue()
        ->and($payrollRecord->expenses()->whereKey($expense->id)->exists())->toBeTrue()
        ->and($category->expenses()->whereKey($expense->id)->exists())->toBeTrue();
});
