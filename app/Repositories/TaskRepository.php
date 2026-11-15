<?php

namespace App\Repositories;

use App\Enums\TaskStatus;
use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskComment;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class TaskRepository implements TaskRepositoryInterface
{
    // Get tasks assigned to the employee and due today.
    public function getTodayTasks(
        Employee $employee,
        int $limit
    ): LengthAwarePaginator {
        return Task::query()
            ->with([
                'assignees.level',
                'assignees.jobTitle',
            ])
            ->withCount('comments')
            ->whereHas('assignees', function (Builder $query) use ($employee) {
                $query->whereKey($employee->getKey());
            })
            ->whereDate('due_date', today())
            ->orderBy('due_date')
            ->paginate($limit);
    }

    // Get task counts grouped by status for the requested scope.
    public function getTaskSummary(
        Employee $employee,
        string $scope
    ): array {
        $counts = $this->getTasksQueryForScope($employee, $scope)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'toDoCount' => (int) ($counts[TaskStatus::ToDo->value] ?? 0),
            'inProgressCount' => (int) ($counts[TaskStatus::InProgress->value] ?? 0),
            'doneCount' => (int) ($counts[TaskStatus::Done->value] ?? 0),
        ];
    }

    // Get paginated tasks for the requested scope.
    public function getTasks(
        Employee $employee,
        string $scope,
        ?TaskStatus $status,
        int $limit
    ): LengthAwarePaginator {
        $query = $this->getTasksQueryForScope($employee, $scope);

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        return $query
            ->with([
                'assignees.level',
                'assignees.jobTitle',
            ])
            ->withCount('comments')
            ->latest()
            ->paginate($limit);
    }

    // Find a task visible to the employee.
    public function findVisibleTask(
        Employee $employee,
        string $taskId
    ): ?Task {
        return Task::query()
            ->with([
                'assignees.level',
                'assignedBy.level',
                'attachments',
            ])
            ->whereKey($taskId)
            ->where(function (Builder $query) use ($employee) {
                $query->where('assigned_by_id', $employee->getKey())
                    ->orWhereHas('assignees', function (Builder $assigneesQuery) use ($employee) {
                        $assigneesQuery->whereKey($employee->getKey());
                    });
            })
            ->first();
    }

    // Get task comments in chronological order.
    public function getTaskComments(
        Task $task,
        int $limit
    ): LengthAwarePaginator {
        return $task->comments()
            ->with('employee.level')
            ->oldest()
            ->paginate($limit);
    }

    // Get employees eligible for task assignment.
    public function getAssignableEmployees(
        Employee $assigner
    ): Collection {
        return Employee::query()
            ->assignableBy($assigner)
            ->with('level')
            ->orderBy('first_name')
            ->get();
    }

    // Create a task.
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    // Synchronize task assignees.
    public function syncAssignees(
        Task $task,
        array $employeeIds
    ): void {
        $task->assignees()->sync($employeeIds);
    }

    // Update a task.
    public function update(
        Task $task,
        array $data
    ): bool {
        return $task->update($data);
    }

    // Find a task for update while locking its database row.
    public function findForUpdate(string $taskId): ?Task
    {
        return Task::query()
            ->whereKey($taskId)
            ->lockForUpdate()
            ->first();
    }

    // Create a comment for a task.
    public function createComment(
        Task $task,
        Employee $employee,
        string $body
    ): TaskComment {
        return $task->comments()->create([
            'employee_id' => $employee->getKey(),
            'body' => $body,
        ]);
    }

    // Create attachment records for a task.
    // Files must already be stored by the Service.
    public function createAttachments(
        Task $task,
        array $attachments
    ): void {
        $task->attachments()->createMany($attachments);
    }

    // Build the base query according to the requested scope.
    private function getTasksQueryForScope(
        Employee $employee,
        string $scope
    ): Builder {
        return match ($scope) {
            'mine' => Task::query()
                ->whereHas('assignees', function (Builder $query) use ($employee) {
                    $query->whereKey($employee->getKey());
                }),

            'assigned' => Task::query()
                ->where('assigned_by_id', $employee->getKey()),

            default => throw new InvalidArgumentException(
                "Unsupported task scope: {$scope}"
            ),
        };
    }
}
