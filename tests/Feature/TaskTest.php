<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Level;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Test Helpers
|--------------------------------------------------------------------------
*/

function createTaskTestEmployee(
    int $rank = 2,
    ?Department $department = null,
    bool $active = true
): Employee {
    $department ??= Department::firstOrCreate([
        'name' => 'IT',
    ]);

    $level = Level::firstOrCreate(
        ['rank' => $rank],
        ['name' => match ($rank) {
            1 => 'Intern',
            2 => 'Junior',
            3 => 'Mid-level',
            4 => 'Senior',
            5 => 'Team Lead',
            default => "Level {$rank}",
        }]
    );

    $jobTitle = JobTitle::firstOrCreate([
        'department_id' => $department->id,
        'name' => "Job Title {$rank}",
    ]);

    return Employee::create([
        'level_id' => $level->id,
        'department_id' => $department->id,
        'job_title_id' => $jobTitle->id,
        'employee_id' => 'EMP-' . fake()->unique()->numerify('####'),
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => fake()->unique()->safeEmail(),
        'phone_number' => fake()->unique()->numerify('01#########'),
        'password' => 'Password123!',
        'full_address' => fake()->address(),
        'is_verified' => true,
        'is_first_login' => false,
        'is_active' => $active,
    ]);
}

function createTaskTestTask(
    Employee $assigner,
    array $assignees = [],
    array $attributes = []
): Task {
    $task = Task::create(array_merge([
        'assigned_by_id' => $assigner->id,
        'title' => 'Test Task',
        'description' => 'Task description',
        'status' => 'toDo',
        'priority' => 'medium',
        'difficulty' => 'easy',
        'progress' => 0,
        'due_date' => now()->addDays(2),
    ], $attributes));

    if ($assignees !== []) {
        $task->assignees()->sync(
            collect($assignees)->pluck('id')->all()
        );
    }

    return $task;
}

function taskTestCreatePayload(Employee $assignee): array
{
    return [
        'title' => 'Implement API endpoint',
        'description' => 'Implement the required API endpoint.',
        'assigneeIds' => [$assignee->id],
        'priority' => 'high',
        'difficulty' => 'medium',
        'dueDate' => now()->addDays(3)->toDateString(),
    ];
}

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

test('all task endpoints require authentication', function () {
    $task = Task::create([
        'assigned_by_id' => createTaskTestEmployee(3)->id,
        'title' => 'Protected Task',
        'description' => 'Protected task description',
        'status' => 'toDo',
        'priority' => 'medium',
        'difficulty' => 'easy',
        'progress' => 0,
        'due_date' => now()->addDay(),
    ]);

    $taskUrl = '/api/v1/tasks/' . $task->id;

    $endpoints = [
        ['GET', '/api/v1/tasks'],
        ['POST', '/api/v1/tasks'],
        ['GET', '/api/v1/tasks/today'],
        ['GET', '/api/v1/tasks/summary'],
        ['GET', '/api/v1/tasks/permissions'],
        ['GET', '/api/v1/tasks/assignable-employees'],
        ['GET', $taskUrl],
        ['GET', $taskUrl . '/comments'],
        ['POST', $taskUrl . '/comments'],
        ['POST', $taskUrl . '/start'],
        ['PATCH', $taskUrl . '/progress'],
        ['POST', $taskUrl . '/complete'],
    ];

    foreach ($endpoints as [$method, $uri]) {
        $this->json($method, $uri)->assertUnauthorized();
    }
});

/*
|--------------------------------------------------------------------------
| Permissions and Assignment Rules
|--------------------------------------------------------------------------
*/

test('permissions endpoint returns whether employee can create tasks', function () {
    $senior = createTaskTestEmployee(4);
    $junior = createTaskTestEmployee(2);

    $this->actingAs($senior, 'sanctum')
        ->getJson('/api/v1/tasks/permissions')
        ->assertOk()
        ->assertJsonPath('data.canCreateTask', true);

    $this->actingAs($junior, 'sanctum')
        ->getJson('/api/v1/tasks/permissions')
        ->assertOk()
        ->assertJsonPath('data.canCreateTask', false);
});

test('employees below the minimum rank cannot create tasks', function () {
    $junior = createTaskTestEmployee(2);
    $assignee = createTaskTestEmployee(1);

    $this->actingAs($junior, 'sanctum')
        ->postJson('/api/v1/tasks', taskTestCreatePayload($assignee))
        ->assertForbidden();

    expect(Task::count())->toBe(0);
});

test('assignable employees are limited to active lower-ranked employees in the same department', function () {
    $department = Department::create(['name' => 'IT']);
    $otherDepartment = Department::create(['name' => 'HR']);

    $assigner = createTaskTestEmployee(4, $department);

    $eligible = createTaskTestEmployee(2, $department);
    $sameRank = createTaskTestEmployee(4, $department);
    $higherRank = createTaskTestEmployee(5, $department);
    $inactive = createTaskTestEmployee(2, $department, false);
    $otherDepartmentEmployee = createTaskTestEmployee(2, $otherDepartment);

    $response = $this->actingAs($assigner, 'sanctum')
        ->getJson('/api/v1/tasks/assignable-employees')
        ->assertOk();

    $ids = collect($response->json('data'))
        ->pluck('id')
        ->all();

    expect($ids)
        ->toContain($eligible->id)
        ->not->toContain($sameRank->id)
        ->not->toContain($higherRank->id)
        ->not->toContain($inactive->id)
        ->not->toContain($otherDepartmentEmployee->id)
        ->not->toContain($assigner->id);
});

test('task creation rejects employees outside the assigner scope', function () {
    $department = Department::create(['name' => 'IT']);
    $otherDepartment = Department::create(['name' => 'HR']);

    $assigner = createTaskTestEmployee(4, $department);
    $invalidAssignee = createTaskTestEmployee(2, $otherDepartment);

    $this->actingAs($assigner, 'sanctum')
        ->postJson('/api/v1/tasks', taskTestCreatePayload($invalidAssignee))
        ->assertForbidden();

    expect(Task::count())->toBe(0);
});

test('task creation rejects duplicate assignee IDs', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $payload = taskTestCreatePayload($assignee);
    $payload['assigneeIds'] = [
        $assignee->id,
        $assignee->id,
    ];

    $this->actingAs($assigner, 'sanctum')
        ->postJson('/api/v1/tasks', $payload)
        ->assertForbidden();

    expect(Task::count())->toBe(0);
});

test('task creation rejects assigning the task to the assigner themselves', function () {
    $assigner = createTaskTestEmployee(4);

    $payload = taskTestCreatePayload($assigner);

    $this->actingAs($assigner, 'sanctum')
        ->postJson('/api/v1/tasks', $payload)
        ->assertForbidden();

    expect(Task::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Task Creation and Attachments
|--------------------------------------------------------------------------
*/

test('eligible employee can create a task and assign it successfully', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $this->actingAs($assigner, 'sanctum')
        ->postJson('/api/v1/tasks', taskTestCreatePayload($assignee))
        ->assertCreated()
        ->assertJsonPath('data.title', 'Implement API endpoint')
        ->assertJsonPath('data.status', 'toDo')
        ->assertJsonPath('data.priority', 'high')
        ->assertJsonPath('data.difficulty', 'medium')
        ->assertJsonPath('data.progress', 0);

    $task = Task::first();

    expect($task)->not->toBeNull()
        ->and((string) $task->assigned_by_id)->toBe((string) $assigner->id)
        ->and($task->assignees()->whereKey($assignee->id)->exists())->toBeTrue()
        ->and($task->status->value)->toBe('toDo')
        ->and($task->progress)->toBe(0);

    expect(
        $assignee->notifications()
            ->where('reference_id', $task->id)
            ->where('type', 'task')
            ->exists()
    )->toBeTrue();
});

test('task creation supports private attachments', function () {
    Storage::fake('local');

    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $payload = taskTestCreatePayload($assignee);
    $payload['attachments'] = [
        UploadedFile::fake()->create(
            'requirements.pdf',
            1024,
            'application/pdf'
        ),
        UploadedFile::fake()->create(
            'screenshot.jpg',
            100,
            'image/jpeg'
        ),
    ];

    $response = $this->actingAs($assigner, 'sanctum')
        ->post('/api/v1/tasks', $payload, [
            'Accept' => 'application/json',
        ]);

    $response->assertCreated();

    $task = Task::first();

    expect($task)->not->toBeNull()
        ->and(TaskAttachment::where('task_id', $task->id)->count())->toBe(2);

    $attachments = TaskAttachment::where('task_id', $task->id)->get();

    foreach ($attachments as $attachment) {
        Storage::disk('local')->assertExists($attachment->file_url);

        expect($attachment->file_name)->not->toBeEmpty()
            ->and($attachment->mime_type)->not->toBeEmpty();
    }
});

test('task creation rejects more than three attachments', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $payload = taskTestCreatePayload($assignee);
    $payload['attachments'] = [
        UploadedFile::fake()->create('one.pdf', 100, 'application/pdf'),
        UploadedFile::fake()->create('two.pdf', 100, 'application/pdf'),
        UploadedFile::fake()->create('three.pdf', 100, 'application/pdf'),
        UploadedFile::fake()->create('four.pdf', 100, 'application/pdf'),
    ];

    $this->actingAs($assigner, 'sanctum')
        ->post('/api/v1/tasks', $payload, [
            'Accept' => 'application/json',
        ])
        ->assertStatus(400);

    expect(Task::count())->toBe(0);
});

test('task creation rejects unsupported attachment types', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $payload = taskTestCreatePayload($assignee);
    $payload['attachments'] = [
        UploadedFile::fake()->create(
            'script.exe',
            100,
            'application/octet-stream'
        ),
    ];

    $this->actingAs($assigner, 'sanctum')
        ->post('/api/v1/tasks', $payload, [
            'Accept' => 'application/json',
        ])
        ->assertStatus(400);

    expect(Task::count())->toBe(0);
});

test('task creation rejects attachments that are five megabytes or larger', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $payload = taskTestCreatePayload($assignee);
    $payload['attachments'] = [
        UploadedFile::fake()->create(
            'large.pdf',
            5120,
            'application/pdf'
        ),
    ];

    $this->actingAs($assigner, 'sanctum')
        ->post('/api/v1/tasks', $payload, [
            'Accept' => 'application/json',
        ])
        ->assertStatus(400);

    expect(Task::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Task Listing, Filtering and Summary
|--------------------------------------------------------------------------
*/

test('employee can list their assigned tasks with pagination metadata', function () {
    $assigner = createTaskTestEmployee(4);
    $employee = createTaskTestEmployee(2);

    foreach (range(1, 3) as $number) {
        createTaskTestTask($assigner, [$employee], [
            'title' => "Task {$number}",
        ]);
    }

    $this->actingAs($employee, 'sanctum')
        ->getJson('/api/v1/tasks?scope=mine&limit=2&page=1')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.page', 1)
        ->assertJsonPath('meta.limit', 2)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.total_pages', 2);
});

test('employee can filter tasks by status', function () {
    $assigner = createTaskTestEmployee(4);
    $employee = createTaskTestEmployee(2);

    createTaskTestTask($assigner, [$employee], [
        'status' => 'toDo',
    ]);

    createTaskTestTask($assigner, [$employee], [
        'status' => 'inProgress',
    ]);

    createTaskTestTask($assigner, [$employee], [
        'status' => 'done',
    ]);

    $this->actingAs($employee, 'sanctum')
        ->getJson('/api/v1/tasks?scope=mine&status=inProgress')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'inProgress');
});

test('employee cannot request assigned scope without sufficient rank', function () {
    $employee = createTaskTestEmployee(2);

    $this->actingAs($employee, 'sanctum')
        ->getJson('/api/v1/tasks?scope=assigned')
        ->assertForbidden();
});

test('eligible employee can list tasks they assigned', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    createTaskTestTask($assigner, [$assignee]);

    $this->actingAs($assigner, 'sanctum')
        ->getJson('/api/v1/tasks?scope=assigned')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('task listing rejects invalid scope and status values', function () {
    $employee = createTaskTestEmployee(3);

    $this->actingAs($employee, 'sanctum')
        ->getJson('/api/v1/tasks?scope=invalid')
        ->assertStatus(400);

    $this->actingAs($employee, 'sanctum')
        ->getJson('/api/v1/tasks?status=invalid')
        ->assertStatus(400);
});

test('task listing rejects limits above one hundred', function () {
    $employee = createTaskTestEmployee(3);

    $this->actingAs($employee, 'sanctum')
        ->getJson('/api/v1/tasks?limit=101')
        ->assertStatus(400);
});

test('task summary returns counts grouped by status', function () {
    $assigner = createTaskTestEmployee(4);
    $employee = createTaskTestEmployee(2);

    createTaskTestTask($assigner, [$employee], [
        'status' => 'toDo',
    ]);

    createTaskTestTask($assigner, [$employee], [
        'status' => 'toDo',
    ]);

    createTaskTestTask($assigner, [$employee], [
        'status' => 'inProgress',
    ]);

    createTaskTestTask($assigner, [$employee], [
        'status' => 'done',
    ]);

    $this->actingAs($employee, 'sanctum')
        ->getJson('/api/v1/tasks/summary?scope=mine')
        ->assertOk()
        ->assertJsonPath('data.toDoCount', 2)
        ->assertJsonPath('data.inProgressCount', 1)
        ->assertJsonPath('data.doneCount', 1);
});

test('today endpoint returns only tasks assigned to the employee and due today', function () {
    $assigner = createTaskTestEmployee(4);
    $employee = createTaskTestEmployee(2);

    createTaskTestTask($assigner, [$employee], [
        'title' => 'Due Today',
        'due_date' => today()->setTime(12, 0),
    ]);

    createTaskTestTask($assigner, [$employee], [
        'title' => 'Due Tomorrow',
        'due_date' => today()->addDay(),
    ]);

    createTaskTestTask($assigner, [], [
        'title' => 'Not Assigned',
        'due_date' => today(),
    ]);

    $this->actingAs($employee, 'sanctum')
        ->getJson('/api/v1/tasks/today')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Due Today');
});

/*
|--------------------------------------------------------------------------
| Task Details and Visibility
|--------------------------------------------------------------------------
*/

test('assigner and assignees can view task details', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);
    $taskUrl = '/api/v1/tasks/' . $task->id;

    $this->actingAs($assigner, 'sanctum')
        ->getJson($taskUrl)
        ->assertOk()
        ->assertJsonPath('data.id', $task->id)
        ->assertJsonPath('data.title', $task->title)
        ->assertJsonPath('data.status', 'toDo');

    $this->actingAs($assignee, 'sanctum')
        ->getJson($taskUrl)
        ->assertOk()
        ->assertJsonPath('data.id', $task->id);
});

test('unrelated employees cannot view task details', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);
    $unrelatedEmployee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    $this->actingAs($unrelatedEmployee, 'sanctum')
        ->getJson('/api/v1/tasks/' . $task->id)
        ->assertForbidden();
});

test('task details return completion information only to the assigner', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee], [
        'status' => 'done',
        'progress' => 100,
        'completion_summary' => 'Completed successfully',
        'completion_notes' => 'All acceptance criteria passed',
        'completed_at' => now(),
    ]);

    $taskUrl = '/api/v1/tasks/' . $task->id;

    $this->actingAs($assigner, 'sanctum')
        ->getJson($taskUrl)
        ->assertOk()
        ->assertJsonPath('data.completion.summary', 'Completed successfully')
        ->assertJsonPath('data.completion.notes', 'All acceptance criteria passed');

    $this->actingAs($assignee, 'sanctum')
        ->getJson($taskUrl)
        ->assertOk()
        ->assertJsonMissingPath('data.completion');
});

/*
|--------------------------------------------------------------------------
| Task Comments
|--------------------------------------------------------------------------
*/

test('assignees can add comments to tasks', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    $this->actingAs($assignee, 'sanctum')
        ->postJson('/api/v1/tasks/' . $task->id . '/comments', [
            'text' => 'Implementation has started.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.text', 'Implementation has started.')
        ->assertJsonPath('data.author.id', $assignee->id);

    expect(TaskComment::where('task_id', $task->id)->count())->toBe(1);
});

test('assigners cannot add comments unless they are also assignees', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    $this->actingAs($assigner, 'sanctum')
        ->postJson('/api/v1/tasks/' . $task->id . '/comments', [
            'text' => 'This should be forbidden.',
        ])
        ->assertForbidden();

    expect(TaskComment::count())->toBe(0);
});

test('assigner and assignees can view task comments', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    TaskComment::create([
        'task_id' => $task->id,
        'employee_id' => $assignee->id,
        'body' => 'Existing comment',
    ]);

    // Assignee can view comments.
    $this->actingAs($assignee, 'sanctum')
        ->getJson('/api/v1/tasks/' . $task->id . '/comments')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.text', 'Existing comment');

    // Assigner can view comments too.
    $this->actingAs($assigner, 'sanctum')
        ->getJson('/api/v1/tasks/' . $task->id . '/comments')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.text', 'Existing comment');
});

test('unrelated employees cannot view task comments', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);
    $unrelated = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    TaskComment::create([
        'task_id' => $task->id,
        'employee_id' => $assignee->id,
        'body' => 'Existing comment',
    ]);

    $this->actingAs($unrelated, 'sanctum')
        ->getJson('/api/v1/tasks/' . $task->id . '/comments')
        ->assertForbidden();
});

test('comment creation validates required text', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    $this->actingAs($assignee, 'sanctum')
        ->postJson('/api/v1/tasks/' . $task->id . '/comments', [
            'text' => '',
        ])
        ->assertStatus(400);

    expect(TaskComment::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Task Workflow
|--------------------------------------------------------------------------
*/

test('assignee can start a task', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    $this->actingAs($assignee, 'sanctum')
        ->postJson('/api/v1/tasks/' . $task->id . '/start')
        ->assertOk()
        ->assertJsonPath('data.status', 'inProgress')
        ->assertJsonPath('data.progress', 0);

    expect($task->fresh()->status->value)->toBe('inProgress');
});

test('only assignees can start tasks', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    $this->actingAs($assigner, 'sanctum')
        ->postJson('/api/v1/tasks/' . $task->id . '/start')
        ->assertForbidden();

    expect($task->fresh()->status->value)->toBe('toDo');
});

test('starting a task with an invalid status returns conflict', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee], [
        'status' => 'inProgress',
    ]);

    $this->actingAs($assignee, 'sanctum')
        ->postJson('/api/v1/tasks/' . $task->id . '/start')
        ->assertStatus(409);
});

test('assignee can update task progress using the API fraction format', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee], [
        'status' => 'inProgress',
    ]);

    $this->actingAs($assignee, 'sanctum')
        ->patchJson('/api/v1/tasks/' . $task->id . '/progress', [
            'progress' => 0.75,
        ])
        ->assertOk()
        ->assertJsonPath('data.progress', 0.75);

    expect($task->fresh()->progress)->toBe(75);
});

test('progress must be between zero and one', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee], [
        'status' => 'inProgress',
    ]);

    $this->actingAs($assignee, 'sanctum')
        ->patchJson('/api/v1/tasks/' . $task->id . '/progress', [
            'progress' => 1.1,
        ])
        ->assertStatus(400);

    $this->actingAs($assignee, 'sanctum')
        ->patchJson('/api/v1/tasks/' . $task->id . '/progress', [
            'progress' => -0.1,
        ])
        ->assertStatus(400);

    expect($task->fresh()->progress)->toBe(0);
});

test('reaching one hundred percent progress does not complete the task automatically', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee], [
        'status' => 'inProgress',
    ]);

    $this->actingAs($assignee, 'sanctum')
        ->patchJson('/api/v1/tasks/' . $task->id . '/progress', [
            'progress' => 1,
        ])
        ->assertOk()
        ->assertJsonPath('data.progress', 1)
        ->assertJsonPath('data.status', 'inProgress');

    expect($task->fresh()->status->value)->toBe('inProgress')
        ->and($task->fresh()->progress)->toBe(100);
});

test('progress cannot be updated before a task is started', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    $this->actingAs($assignee, 'sanctum')
        ->patchJson('/api/v1/tasks/' . $task->id . '/progress', [
            'progress' => 0.5,
        ])
        ->assertStatus(409);
});

test('assignee can complete an in-progress task', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee], [
        'status' => 'inProgress',
        'progress' => 50,
    ]);

    $this->actingAs($assignee, 'sanctum')
        ->postJson('/api/v1/tasks/' . $task->id . '/complete', [
            'summary' => 'Task completed successfully',
            'notes' => 'All requirements have been implemented.',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'done')
        ->assertJsonPath('data.progress', 1);

    $task->refresh();

    expect($task->status->value)->toBe('done')
        ->and($task->progress)->toBe(100)
        ->and($task->completion_summary)->toBe('Task completed successfully')
        ->and($task->completion_notes)->toBe('All requirements have been implemented.')
        ->and($task->completed_at)->not->toBeNull();

    expect(
        $assigner->notifications()
            ->where('reference_id', $task->id)
            ->where('title', 'Task completed')
            ->exists()
    )->toBeTrue();
});

test('task cannot be completed before it is in progress', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    $this->actingAs($assignee, 'sanctum')
        ->postJson('/api/v1/tasks/' . $task->id . '/complete', [
            'summary' => 'Completed',
        ])
        ->assertStatus(409);

    expect($task->fresh()->status->value)->toBe('toDo');
});

test('completion requires a summary', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee], [
        'status' => 'inProgress',
    ]);

    $this->actingAs($assignee, 'sanctum')
        ->postJson('/api/v1/tasks/' . $task->id . '/complete', [
            'summary' => '',
        ])
        ->assertStatus(400);

    expect($task->fresh()->status->value)->toBe('inProgress');
});

/*
|--------------------------------------------------------------------------
| Sprint Relationship
|--------------------------------------------------------------------------
*/

test('task can be associated with a sprint', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $sprint = Sprint::create([
        'name' => 'Sprint 1',
        'start_date' => today()->toDateString(),
        'end_date' => today()->addDays(14)->toDateString(),
    ]);

    $task = createTaskTestTask($assigner, [$assignee], [
        'sprint_id' => $sprint->id,
    ]);

    expect((string) $task->sprint_id)->toBe((string) $sprint->id)
        ->and($task->sprint->name)->toBe('Sprint 1')
        ->and($sprint->tasks()->whereKey($task->id)->exists())->toBeTrue();
});

test('task details return signed media URLs for attachments', function () {
    $assigner = createTaskTestEmployee(4);
    $assignee = createTaskTestEmployee(2);

    $task = createTaskTestTask($assigner, [$assignee]);

    // Store a real file on the local disk (not fake).
    $path = Storage::disk('local')->putFile(
        "tasks/{$task->id}",
        UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')
    );

    // Create the attachment record.
    TaskAttachment::create([
        'task_id' => $task->id,
        'file_url' => $path,
        'file_name' => 'document.pdf',
        'mime_type' => 'application/pdf',
    ]);

    // Fetch task details as the assignee.
    $response = $this->actingAs($assignee, 'sanctum')
        ->getJson('/api/v1/tasks/' . $task->id)
        ->assertOk();

    // Assert that mediaUrls is a non-empty array of signed URLs.
    $mediaUrls = $response->json('data.mediaUrls');

    expect($mediaUrls)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($mediaUrls[0])
        ->toBeString()
        ->toContain('http')                       // Signed URL should start with http
        ->toContain('expires=')                   // Signed URL contains expires param
        ->toContain('signature=');                // Signed URL contains signature param

    // Cleanup.
    Storage::disk('local')->delete($path);
});