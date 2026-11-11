<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\TaskIndexRequest;
use App\Http\Requests\Tasks\TaskSummaryRequest;
use App\Http\Requests\Tasks\TaskTodayRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\EmployeeSummaryResource;
use App\Http\Resources\Tasks\TaskDetailResource;
use App\Http\Resources\Tasks\TaskResource;
use App\Http\Resources\Tasks\TaskSummaryResource;
use App\Models\Task;
use App\Services\Task\Interfaces\TaskManagementServiceInterface;
use App\Services\Task\Interfaces\TaskQueryServiceInterface;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(
        protected TaskQueryServiceInterface $queryService,
        protected TaskManagementServiceInterface $managementService,
    ) {}

    /**
     * Get today's tasks.
     */
    public function today(TaskTodayRequest $request)
    {
        $employee = Auth::user();

        $tasks = $this->queryService->getTodayTasks(
            $employee,
            (int) $request->validated('limit')
        );

        return (new ApiCollection($tasks, TaskResource::class))
            ->toResponse($request);
    }

    /**
     * Get task summary.
     */
    public function summary(TaskSummaryRequest $request)
    {
        $employee = Auth::user();

        $summary = $this->queryService->getSummary(
            $employee,
            $request->validated('scope')
        );

        return ApiResponse::success(
            new TaskSummaryResource($summary)
        );
    }

    /**
     * Get tasks.
     */
    public function index(TaskIndexRequest $request)
    {
        $employee = Auth::user();
        $validated = $request->validated();

        $tasks = $this->queryService->getTasks(
            $employee,
            $validated['scope'],
            isset($validated['status'])
                ? \App\Enums\TaskStatus::from($validated['status'])
                : null,
            (int) $validated['limit']
        );

        return (new ApiCollection($tasks, TaskResource::class))
            ->toResponse($request);
    }

    /**
     * Get task details.
     */
    public function show(Task $task)
    {
        Gate::authorize('view', $task);

        $employee = Auth::user();

        $task = $this->queryService->getTaskDetails(
            (string) $task->getKey(),
            $employee
        );

        return ApiResponse::success(
            new TaskDetailResource($task)
        );
    }

    /**
     * Get current employee task permissions.
     */
    public function permissions()
    {
        $employee = Auth::user();

        return ApiResponse::success(
            $this->queryService->getPermissions($employee)
        );
    }

    /**
     * Get employees assignable by the current employee.
     */
    public function assignableEmployees()
    {
        $employee = Auth::user();

        $employees = $this->queryService->getAssignableEmployees($employee);

        return ApiResponse::success(
            EmployeeSummaryResource::collection($employees)
        );
    }

    /**
     * Create a task.
     */
    public function store(StoreTaskRequest $request)
    {
        Gate::authorize('create', Task::class);

        $employee = Auth::user();

        $task = $this->managementService->createTask(
            $employee,
            $request->validated()
        );

        return ApiResponse::created(
            new TaskDetailResource($task)
        );
    }
}
