<?php

namespace App\Services\Task;

use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskComment;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Services\Task\Interfaces\TaskCommentServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class TaskCommentService implements TaskCommentServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    // Get paginated comments for a task.
    public function getComments(
        Task $task,
        int $limit
    ): LengthAwarePaginator {
        return $this->taskRepository->getTaskComments(
            $task,
            $limit
        );
    }

    // Add a comment to a task.
    public function addComment(
        Task $task,
        Employee $employee,
        string $body
    ): TaskComment {
        return $this->taskRepository->createComment(
            $task,
            $employee,
            $body
        );
    }
}
