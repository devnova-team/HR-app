<?php

namespace App\Models;

use App\Enums\TaskDifficulty;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends BaseModel
{
    protected $fillable = [
        'assigned_by_id',
        'sprint_id',
        'title',
        'description',
        'status',
        'priority',
        'difficulty',
        'progress',
        'story_points',
        'due_date',
        'completed_at',
        'completion_summary',
        'completion_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'difficulty' => TaskDifficulty::class,
            'progress' => 'integer',
            'story_points' => 'integer',
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Relationships

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_by_id');
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(
            Employee::class,
            'task_assignees',
            'task_id',
            'employee_id'
        );
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }
}
