<?php

namespace App\Services\Task\Interfaces;

use App\Models\Task;


interface TaskWorkflowServiceInterface
{
    // Start a task.
    public function startTask(
        Task $task
    ): Task;

    // Update task progress.
    public function updateProgress(
        Task $task,
        float $progress
    ): Task;

    // Complete a task with its completion report.
    public function completeTask(
        Task $task,
        string $summary,
        ?string $notes = null
    ): Task;
}
