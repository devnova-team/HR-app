<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use Database\Factories\LeaveRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends BaseModel
{
    /** @use HasFactory<LeaveRequestFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => LeaveStatus::Review->value,
    ];

    protected $fillable = [
        'employee_id',
        'leave_category_id',
        'task_delegate_id',
        'start_date',
        'end_date',
        'days_count',
        'description',
        'emergency_contact_name',
        'emergency_contact_phone',
        'status',
        'reviewed_by_id',
        'reviewed_at',
        'rejection_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'days_count' => 'float',
            'status' => LeaveStatus::class,
            'reviewed_at' => 'datetime',
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

    public function taskDelegate(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'task_delegate_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewed_by_id');
    }
}
