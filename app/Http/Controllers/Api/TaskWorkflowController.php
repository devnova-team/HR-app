<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\CompleteTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskProgressRequest;
use App\Http\Resources\Tasks\TaskDetailResource;
use App\Models\Task;
use App\Services\Task\Interfaces\TaskWorkflowServiceInterface;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Gate;

class TaskWorkflowController extends Controller
{
    
    public function __construct(
        protected TaskWorkflowServiceInterface $workflowService
    ) {}

    /**
     * Start a task.
     */
    public function start(Task $task)
    {
        Gate::authorize('start', $task);

        $updatedTask = $this->workflowService->startTask($task);

        return ApiResponse::success(
            new TaskDetailResource($updatedTask)
        );
    }

    /**
     * Update task progress.
     */
    public function updateProgress(
        UpdateTaskProgressRequest $request,
        Task $task
    ) {
        Gate::authorize('updateProgress', $task);

        $updatedTask = $this->workflowService->updateProgress(
            $task,
            (float) $request->validated('progress')
        );

        return ApiResponse::success(
            new TaskDetailResource($updatedTask)
        );
    }

    /**
     * Complete a task.
     */
    public function complete(
        CompleteTaskRequest $request,
        Task $task
    ) {
        Gate::authorize('complete', $task);

        $validated = $request->validated();

        $updatedTask = $this->workflowService->completeTask(
            $task,
            $validated['summary'],
            $validated['notes'] ?? null
        );

        return ApiResponse::success(
            new TaskDetailResource($updatedTask)
        );
    }
}
