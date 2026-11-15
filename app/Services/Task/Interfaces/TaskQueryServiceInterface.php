<?php

namespace App\Services\Task\Interfaces;

use App\Enums\TaskStatus;
use App\Models\Employee;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskQueryServiceInterface
{
    // Get tasks assigned to the employee and due today.
    public function getTodayTasks(
        Employee $employee,
        int $limit
    ): LengthAwarePaginator;

    // Get task counts grouped by status.
    public function getSummary(
        Employee $employee,
        string $scope
    ): array;

    // Get paginated tasks according to the requested scope and status.
    public function getTasks(
        Employee $employee,
        string $scope,
        ?TaskStatus $status,
        int $limit
    ): LengthAwarePaginator;

    // Get task details visible to the employee.
    public function getTaskDetails(
        string $taskId,
        Employee $employee
    ): Task;

    // Get task permissions for the employee.
    public function getPermissions(
        Employee $employee
    ): array;

    // Get employees eligible for task assignment.
    public function getAssignableEmployees(
        Employee $employee
    ): Collection;
}
