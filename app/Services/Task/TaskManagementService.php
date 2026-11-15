<?php

namespace App\Services\Task;

use App\Enums\TaskStatus;
use App\Models\Employee;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Services\Task\Interfaces\TaskManagementServiceInterface;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class TaskManagementService implements TaskManagementServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private NotificationService $notificationService
    ) {}

    // Create a task, assign eligible employees, and handle its attachments.
    public function createTask(
        Employee $employee,
        array $data
    ): Task {
        $assigneeIds = $data['assigneeIds'];
        $attachments = $data['attachments'] ?? [];
        $storedFiles = [];


        try {
            return DB::transaction(function () use (
                $employee,
                $data,
                $assigneeIds,
                $attachments,
                &$storedFiles
            ) {
                // Reject duplicate assignee IDs.
                if (
                    count($assigneeIds)
                    !== count(array_unique($assigneeIds))
                ) {
                    throw new AuthorizationException(
                        'One or more selected employees cannot be assigned to this task.'
                    );
                }

                // Validate all assignees using the shared eligibility scope.
                $assignableIds = $this->taskRepository
                    ->getAssignableEmployees($employee)
                    ->modelKeys();

                $invalidAssigneeIds = array_diff(
                    $assigneeIds,
                    $assignableIds
                );

                if (! empty($invalidAssigneeIds)) {
                    throw new AuthorizationException(
                        'One or more selected employees cannot be assigned to this task.'
                    );
                }

                $task = $this->taskRepository->create([
                    'assigned_by_id' => $employee->getKey(),
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'status' => TaskStatus::ToDo->value,
                    'priority' => $data['priority'],
                    'difficulty' => $data['difficulty'],
                    'progress' => 0,
                    'due_date' => $data['dueDate'],
                ]);

                $this->taskRepository->syncAssignees(
                    $task,
                    $assigneeIds
                );

                $attachmentRecords = [];

                foreach ($attachments as $attachment) {
                    if (! $attachment instanceof UploadedFile) {
                        continue;
                    }

                    $path = $attachment->store(
                        "tasks/{$task->getKey()}",
                        'local'
                    );

                    if ($path === false) {
                        throw new RuntimeException(
                            'Failed to store task attachment.'
                        );
                    }

                    $storedFiles[] = $path;

                    $attachmentRecords[] = [
                        'file_url' => $path,
                        'file_name' => $attachment->getClientOriginalName(),
                        'mime_type' => $attachment->getMimeType(),
                    ];
                }

                if (! empty($attachmentRecords)) {
                    $this->taskRepository->createAttachments(
                        $task,
                        $attachmentRecords
                    );
                }
                
                // Load task relationships before sending notifications.
                $task->load([
                    'assignees.level',
                    'assignedBy.level',
                    'attachments',
                ]);

                // Notify every employee assigned to the task.
                foreach ($task->assignees as $assignee) {
                    $this->notificationService->send(
                        $assignee,
                        'New task assigned',
                        "You have been assigned to the task \"{$task->title}\".",
                        'task',
                        (string) $task->getKey()
                    );
                }

                return $task;
            });
        } catch (Throwable $exception) {
            if (! empty($storedFiles)) {
                Storage::disk('local')->delete($storedFiles);
            }

            throw $exception;
        }
    }
}
