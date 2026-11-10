<?php

namespace App\Models;

use Database\Factories\LeaveCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveCategory extends BaseModel
{
    /** @use HasFactory<LeaveCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'default_days',
        'is_paid',
        'requires_balance',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_days' => 'float',
            'is_paid' => 'boolean',
            'requires_balance' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function balances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
