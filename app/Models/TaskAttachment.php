<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends BaseModel
{
    public const UPDATED_AT = null;
    
    protected $fillable = [
        'task_id',
        'file_url',
        'file_name',
        'mime_type',
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

    public function getSignedUrlAttribute(): string
    {
        return Storage::disk("local")->temporaryUrl(
            $this->file_url,
            now()->addMinutes(5)
        );
    }
}
