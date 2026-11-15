<?php

namespace App\Repositories\Interfaces;

use App\Enums\TaskStatus;
use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function getTodayTasks(
        Employee $employee,
        int $limit
    ): LengthAwarePaginator;

    public function getTaskSummary(
        Employee $employee,
        string $scope
    ): array;

    public function getTasks(
        Employee $employee,
        string $scope,
        ?TaskStatus $status,
        int $limit
    ): LengthAwarePaginator;

    public function findVisibleTask(
        Employee $employee,
        string $taskId
    ): ?Task;

    public function getTaskComments(
        Task $task,
        int $limit
    ): LengthAwarePaginator;

    public function getAssignableEmployees(
        Employee $assigner
    ): Collection;

    public function create(array $data): Task;

    public function syncAssignees(
        Task $task,
        array $employeeIds
    ): void;

    public function update(
        Task $task,
        array $data
    ): bool;

    public function findForUpdate(
        string $taskId
    ): ?Task;

    public function createComment(
        Task $task,
        Employee $employee,
        string $body
    ): TaskComment;

    public function createAttachments(
        Task $task,
        array $attachments
    ): void;
}
