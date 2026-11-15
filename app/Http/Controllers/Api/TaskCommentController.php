<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskCommentRequest;
use App\Http\Requests\Tasks\TaskCommentIndexRequest;
use App\Http\Resources\Tasks\TaskCommentResource;
use App\Models\Task;
use App\Services\Task\Interfaces\TaskCommentServiceInterface;
use App\Support\ApiResponse;
use App\Http\Resources\ApiCollection;
use Illuminate\Support\Facades\Gate;


class TaskCommentController extends Controller
{
    public function __construct(
        protected TaskCommentServiceInterface $commentService
    ) {}

    public function index(TaskCommentIndexRequest $request, Task $task)
    {
        Gate::authorize('viewComments', $task);

        $comments = $this->commentService->getComments(
            $task,
            (int) $request->validated('limit', 20)
        );

        return ApiResponse::success(
            new ApiCollection($comments, TaskCommentResource::class)
        );
    }

    public function store(StoreTaskCommentRequest $request, Task $task)
    {
        Gate::authorize('comment', $task);

        $comment = $this->commentService->addComment(
            $task,
            $request->user(),
            $request->validated('text')
        );

        return ApiResponse::created(
            new TaskCommentResource($comment)
        );
    }
}
