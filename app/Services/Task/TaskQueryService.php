<?php

namespace App\Services\Task;

use App\Enums\TaskStatus;
use App\Models\Employee;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Services\Task\Interfaces\TaskQueryServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskQueryService implements TaskQueryServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    // Get tasks assigned to the employee and due today.
    public function getTodayTasks(
        Employee $employee,
        int $limit
    ): LengthAwarePaginator {
        return $this->taskRepository->getTodayTasks(
            $employee,
            $limit
        );
    }

    // Get task counts grouped by status.
    public function getSummary(
        Employee $employee,
        string $scope
    ): array {
        $this->ensureAssignedScopeAllowed($employee, $scope);

        return $this->taskRepository->getTaskSummary(
            $employee,
            $scope
        );
    }
    
    // Get paginated tasks according to the requested scope and status.
    public function getTasks(
        Employee $employee,
        string $scope,
        ?TaskStatus $status,
        int $limit
    ): LengthAwarePaginator {
        $this->ensureAssignedScopeAllowed($employee, $scope);

        return $this->taskRepository->getTasks(
            $employee,
            $scope,
            $status,
            $limit
        );
    }

    // Get task details visible to the employee.
    public function getTaskDetails(
        string $taskId,
        Employee $employee
    ): Task {
        $task = $this->taskRepository->findVisibleTask(
            $employee,
            $taskId
        );

        if ($task === null) {
            throw new ModelNotFoundException('Task not found.');
        }

        return $task;
    }

    // Get task permissions for the employee.
    public function getPermissions(Employee $employee): array
    {
        return [
            'canCreateTask' => $employee->canAssignTasks(),
        ];
    }

    // Get employees eligible for task assignment.
    public function getAssignableEmployees(
        Employee $employee
    ): Collection {
        return $this->taskRepository->getAssignableEmployees(
            $employee
        );
    }

    // Ensure the employee is allowed to view tasks they assigned.
    private function ensureAssignedScopeAllowed(
        Employee $employee,
        string $scope
    ): void {
        if ($scope === 'assigned' && ! $employee->canAssignTasks()) {
            throw new AuthorizationException(
                'You are not allowed to view assigned tasks.'
            );
        }
    }
}
