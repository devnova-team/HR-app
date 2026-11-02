<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'file_url',
        'file_name',
        'mime_type',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // Relationships

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
