<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAsset extends BaseModel
{
    public $timestamps = false;

    protected $fillable = ['employee_id', 'asset_id', 'assigned_at', 'returned_at'];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** Assets the employee still holds (not returned). */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereNull('returned_at');
    }
}