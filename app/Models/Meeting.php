<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Meeting extends BaseModel
{
    protected $fillable = [
        'created_by_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'meeting_url',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];



    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'meeting_participants');
    }
}
