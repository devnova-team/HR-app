<?php

namespace App\Services\Task;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Services\NotificationService;
use App\Services\Task\Interfaces\TaskWorkflowServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;


class TaskWorkflowService implements TaskWorkflowServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private NotificationService $notificationService
    )
    {}

    // Start a task.
    public function startTask(Task $task): Task
    {
        $updatedTask = DB::transaction(function () use ($task): Task {
            $lockedTask = $this->findTaskForUpdate($task);

            if ($lockedTask->status !== TaskStatus::ToDo) {
                throw new ConflictHttpException(
                    'Only tasks with toDo status can be started.'
                );
            }

            $this->taskRepository->update($lockedTask, [
                'status' => TaskStatus::InProgress,
                'progress' => 0,
            ]);

            return $lockedTask;
        });

        return $updatedTask->refresh();
    }

    // Update task progress.
    public function updateProgress(
        Task $task,
        float $progress
    ): Task {
        $updatedTask = DB::transaction(function () use (
            $task,
            $progress
        ): Task {
            $lockedTask = $this->findTaskForUpdate($task);

            if ($lockedTask->status !== TaskStatus::InProgress) {
                throw new ConflictHttpException(
                    'Progress can only be updated for in-progress tasks.'
                );
            }

            $this->taskRepository->update($lockedTask, [
                'progress' => (int) round($progress * 100),
            ]);

            return $lockedTask;
        });

        return $updatedTask->refresh();
    }

    // Complete a task with its completion report.
    public function completeTask(
        Task $task,
        string $summary,
        ?string $notes = null
    ): Task {
        $updatedTask = DB::transaction(function () use (
            $task,
            $summary,
            $notes
        ): Task {
            $lockedTask = $this->findTaskForUpdate($task);

            if ($lockedTask->status !== TaskStatus::InProgress) {
                throw new ConflictHttpException(
                    'Only in-progress tasks can be completed.'
                );
            }

            $this->taskRepository->update($lockedTask, [
                'status' => TaskStatus::Done,
                'progress' => 100,
                'completion_summary' => $summary,
                'completion_notes' => $notes,
                'completed_at' => now(),
            ]);

            // Notify the employee who assigned the task.
            $lockedTask->loadMissing('assignedBy');

            $this->notificationService->send(
                $lockedTask->assignedBy,
                'Task completed',
                "The task \"{$lockedTask->title}\" has been completed.",
                'task',
                (string) $lockedTask->getKey()
            );

            return $lockedTask;
        });

        return $updatedTask->refresh();
    }

    // Retrieve and lock the latest task record for a state transition.
    private function findTaskForUpdate(Task $task): Task
    {
        $lockedTask = $this->taskRepository->findForUpdate(
            (string) $task->getKey()
        );

        if ($lockedTask === null) {
            throw (new ModelNotFoundException())->setModel(
                Task::class,
                [$task->getKey()]
            );
        }

        return $lockedTask;
    }
}
