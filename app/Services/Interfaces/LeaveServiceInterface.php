<?php

namespace App\Services\Interfaces;

use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LeaveServiceInterface
{
    public function listEmployeeLeaves(
        Employee $employee,
        array $filters = [],
        int $limit = 20
    ): LengthAwarePaginator;

    public function getLeaveDetails(Employee $employee, string $id): LeaveRequest;

    public function submitLeaveRequest(Employee $employee, array $data): LeaveRequest;

    public function reviewLeaveRequest(
        Employee $reviewer,
        string $id,
        LeaveStatus $status,
        ?string $rejectionReason = null
    ): LeaveRequest;

    public function listActiveCategories(): Collection;

    public function getEmployeeBalances(Employee $employee, ?int $year = null): Collection;
}
