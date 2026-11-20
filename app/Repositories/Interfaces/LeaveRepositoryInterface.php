<?php

namespace App\Repositories\Interfaces;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveCategory;
use App\Models\LeaveRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LeaveRepositoryInterface
{
    public function getForEmployee(
        Employee $employee,
        array $filters = [],
        int $limit = 20
    ): LengthAwarePaginator;

    public function findById(string $id): ?LeaveRequest;

    public function findForEmployee(Employee $employee, string $id): ?LeaveRequest;

    public function create(array $data): LeaveRequest;

    public function update(LeaveRequest $request, array $data): LeaveRequest;

    public function getActiveCategories(): Collection;

    public function getEmployeeBalances(Employee $employee, ?int $year = null): Collection;

    public function getBalance(string $employeeId, string $categoryId, int $year): ?EmployeeLeaveBalance;

    public function hasOverlappingRequests(
        string $employeeId,
        string $startDate,
        string $endDate,
        ?string $ignoreId = null
    ): bool;

    public function findCategoryById(string $id): ?LeaveCategory;
}
