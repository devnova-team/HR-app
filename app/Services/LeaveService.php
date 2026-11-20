<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Repositories\Interfaces\LeaveRepositoryInterface;
use App\Services\Interfaces\LeaveServiceInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class LeaveService implements LeaveServiceInterface
{
    public function __construct(
        protected LeaveRepositoryInterface $repository
    ) {}

    public function listEmployeeLeaves(
        Employee $employee,
        array $filters = [],
        int $limit = 20
    ): LengthAwarePaginator {
        return $this->repository->getForEmployee($employee, $filters, $limit);
    }

    public function getLeaveDetails(Employee $employee, string $id): LeaveRequest
    {
        $request = $this->repository->findForEmployee($employee, $id);

        if (! $request) {
            throw new NotFoundHttpException('Leave request not found.');
        }

        return $request;
    }

    public function submitLeaveRequest(Employee $employee, array $data): LeaveRequest
    {
        $categoryId = $data['leave_category_id'] ?? $data['category_id'];
        $category = $this->repository->findCategoryById($categoryId);

        if (! $category || ! $category->is_active) {
            throw new UnprocessableEntityHttpException('Selected leave category is invalid or inactive.');
        }

        $delegateId = $data['task_delegate_id'] ?? null;
        if ($delegateId && $delegateId === $employee->id) {
            throw new UnprocessableEntityHttpException('You cannot assign yourself as the task delegate.');
        }

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lt($startDate)) {
            throw new UnprocessableEntityHttpException('End date cannot be earlier than start date.');
        }

        if ($this->repository->hasOverlappingRequests($employee->id, $startDate->toDateTimeString(), $endDate->toDateTimeString())) {
            throw new UnprocessableEntityHttpException('You already have a pending or approved leave request during this date range.');
        }

        // Calculate days if omitted: inclusive calendar days
        $daysCount = isset($data['days_count'])
            ? (float) $data['days_count']
            : max(1, $startDate->diffInDays($endDate) + 1);

        $year = (int) $startDate->format('Y');

        if ($category->requires_balance) {
            $balance = $this->repository->getBalance($employee->id, $category->id, $year);

            if (! $balance || $balance->remaining_days < $daysCount) {
                $available = $balance ? $balance->remaining_days : 0;
                throw new UnprocessableEntityHttpException(
                    "Insufficient leave balance for {$category->name}. Available: {$available} days, requested: {$daysCount} days."
                );
            }
        }

        $leaveData = [
            'employee_id' => $employee->id,
            'leave_category_id' => $category->id,
            'task_delegate_id' => $delegateId,
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString(),
            'days_count' => $daysCount,
            'description' => $data['description'],
            'emergency_contact_name' => $data['emergency_contact_name'],
            'emergency_contact_phone' => $data['emergency_contact_phone'],
            'status' => LeaveStatus::Review->value,
        ];

        return $this->repository->create($leaveData)->load('leaveCategory');
    }

    public function reviewLeaveRequest(
        Employee $reviewer,
        string $id,
        LeaveStatus $status,
        ?string $rejectionReason = null
    ): LeaveRequest {
        $leaveRequest = $this->repository->findById($id);

        if (! $leaveRequest) {
            throw new NotFoundHttpException('Leave request not found.');
        }

        if ($leaveRequest->status !== LeaveStatus::Review) {
            throw new UnprocessableEntityHttpException('Leave request has already been processed.');
        }

        return DB::transaction(function () use ($leaveRequest, $reviewer, $status, $rejectionReason) {
            if ($status === LeaveStatus::Approved && $leaveRequest->leaveCategory->requires_balance) {
                $year = (int) Carbon::parse($leaveRequest->start_date)->format('Y');
                $balance = $this->repository->getBalance(
                    $leaveRequest->employee_id,
                    $leaveRequest->leave_category_id,
                    $year
                );

                if ($balance) {
                    $balance->increment('used_days', (float) $leaveRequest->days_count);
                }
            }

            return $this->repository->update($leaveRequest, [
                'status' => $status->value,
                'reviewed_by_id' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $status === LeaveStatus::Rejected ? $rejectionReason : null,
            ]);
        });
    }

    public function listActiveCategories(): Collection
    {
        return $this->repository->getActiveCategories();
    }

    public function getEmployeeBalances(Employee $employee, ?int $year = null): Collection
    {
        return $this->repository->getEmployeeBalances($employee, $year);
    }
}
