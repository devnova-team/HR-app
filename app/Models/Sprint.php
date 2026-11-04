<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprint extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    // Relationships

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
