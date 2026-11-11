<?php

namespace App\Services\Task\Interfaces;

use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


interface TaskCommentServiceInterface
{
    // Get paginated comments for a task.
    public function getComments(
        Task $task,
        int $limit
    ): LengthAwarePaginator;

    // Add a comment to a task.
    public function addComment(
        Task $task,
        Employee $employee,
        string $body
    ): TaskComment;
}
