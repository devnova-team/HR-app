<?php

namespace App\Repositories;

use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveCategory;
use App\Models\LeaveRequest;
use App\Repositories\Interfaces\LeaveRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LeaveRepository implements LeaveRepositoryInterface
{
    public function getForEmployee(
        Employee $employee,
        array $filters = [],
        int $limit = 20
    ): LengthAwarePaginator {
        $query = LeaveRequest::query()
            ->with(['leaveCategory', 'taskDelegate', 'reviewedBy'])
            ->where('employee_id', $employee->id)
            ->latest('start_date');

        if (! empty($filters['status'])) {
            $status = $filters['status'] instanceof LeaveStatus
                ? $filters['status']->value
                : $filters['status'];
            $query->where('status', $status);
        }

        if (! empty($filters['category_id']) || ! empty($filters['leave_category_id'])) {
            $categoryId = $filters['category_id'] ?? $filters['leave_category_id'];
            $query->where('leave_category_id', $categoryId);
        }

        if (! empty($filters['year'])) {
            $query->whereYear('start_date', $filters['year']);
        }

        return $query->paginate($limit);
    }

    public function findById(string $id): ?LeaveRequest
    {
        return LeaveRequest::with(['leaveCategory', 'employee', 'taskDelegate', 'reviewedBy'])
            ->find($id);
    }

    public function findForEmployee(Employee $employee, string $id): ?LeaveRequest
    {
        return LeaveRequest::with(['leaveCategory', 'taskDelegate', 'reviewedBy'])
            ->where('employee_id', $employee->id)
            ->find($id);
    }

    public function create(array $data): LeaveRequest
    {
        return LeaveRequest::create($data);
    }

    public function update(LeaveRequest $request, array $data): LeaveRequest
    {
        $request->update($data);

        return $request->fresh(['leaveCategory', 'employee', 'taskDelegate', 'reviewedBy']);
    }

    public function getActiveCategories(): Collection
    {
        return LeaveCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getEmployeeBalances(Employee $employee, ?int $year = null): Collection
    {
        $year = $year ?: (int) now()->format('Y');

        return EmployeeLeaveBalance::query()
            ->with('leaveCategory')
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->get();
    }

    public function getBalance(string $employeeId, string $categoryId, int $year): ?EmployeeLeaveBalance
    {
        return EmployeeLeaveBalance::query()
            ->where('employee_id', $employeeId)
            ->where('leave_category_id', $categoryId)
            ->where('year', $year)
            ->first();
    }

    public function hasOverlappingRequests(
        string $employeeId,
        string $startDate,
        string $endDate,
        ?string $ignoreId = null
    ): bool {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereIn('status', [LeaveStatus::Review->value, LeaveStatus::Approved->value])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
            ->exists();
    }

    public function findCategoryById(string $id): ?LeaveCategory
    {
        return LeaveCategory::find($id);
    }
}
