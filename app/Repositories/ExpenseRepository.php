<?php

namespace App\Repositories;

use App\Enums\ExpenseStatus;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function getForEmployee(
        Employee $employee,
        array $filters = [],
        int $limit = 20
    ): LengthAwarePaginator {
        $query = Expense::query()
            ->with(['expenseCategory', 'reviewedBy'])
            ->where('employee_id', $employee->id)
            ->latest('transaction_date');

        if (! empty($filters['status'])) {
            $status = $filters['status'] instanceof ExpenseStatus
                ? $filters['status']->value
                : $filters['status'];
            $query->where('status', $status);
        }

        if (! empty($filters['category_id'])) {
            $query->where('expense_category_id', $filters['category_id']);
        }

        return $query->paginate($limit);
    }

    public function findById(string $id): ?Expense
    {
        return Expense::with(['expenseCategory', 'employee', 'reviewedBy', 'payrollRecord'])
            ->find($id);
    }

    public function findForEmployee(Employee $employee, string $id): ?Expense
    {
        return Expense::with(['expenseCategory', 'reviewedBy', 'payrollRecord'])
            ->where('employee_id', $employee->id)
            ->find($id);
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense->fresh(['expenseCategory', 'employee', 'reviewedBy', 'payrollRecord']);
    }

    public function getActiveCategories(): Collection
    {
        return ExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getAllPending(int $limit = 20): LengthAwarePaginator
    {
        return Expense::query()
            ->with(['expenseCategory', 'employee'])
            ->where('status', ExpenseStatus::Review->value)
            ->orderBy('transaction_date')
            ->paginate($limit);
    }
}
