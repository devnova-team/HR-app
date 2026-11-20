<?php

namespace App\Services\Interfaces;

use App\Enums\ExpenseStatus;
use App\Models\Employee;
use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseServiceInterface
{
    public function listEmployeeExpenses(
        Employee $employee,
        array $filters = [],
        int $limit = 20
    ): LengthAwarePaginator;

    public function getExpenseDetails(Employee $employee, string $id): Expense;

    public function submitExpense(Employee $employee, array $data): Expense;

    public function reviewExpense(
        Employee $reviewer,
        string $id,
        ExpenseStatus $status,
        ?string $rejectionReason = null
    ): Expense;

    public function listActiveCategories(): Collection;
}
