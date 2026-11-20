<?php

namespace App\Repositories\Interfaces;

use App\Models\Employee;
use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseRepositoryInterface
{
    public function getForEmployee(
        Employee $employee,
        array $filters = [],
        int $limit = 20
    ): LengthAwarePaginator;

    public function findById(string $id): ?Expense;

    public function findForEmployee(Employee $employee, string $id): ?Expense;

    public function create(array $data): Expense;

    public function update(Expense $expense, array $data): Expense;

    public function getActiveCategories(): Collection;

    public function getAllPending(int $limit = 20): LengthAwarePaginator;
}
