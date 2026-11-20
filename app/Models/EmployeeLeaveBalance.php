<?php

namespace App\Models;

use Database\Factories\EmployeeLeaveBalanceFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBalance extends BaseModel
{
    /** @use HasFactory<EmployeeLeaveBalanceFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_category_id',
        'year',
        'total_days',
        'used_days',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'total_days' => 'float',
            'used_days' => 'float',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveCategory(): BelongsTo
    {
        return $this->belongsTo(LeaveCategory::class);
    }

    protected function remainingDays(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round((float) $this->total_days - (float) $this->used_days, 2),
        );
    }
}
