<?php

namespace App\Models;

use App\Enums\ClockStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends BaseModel
{
    protected $fillable = [
        'employee_id',
        'work_schedule_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'selfie_url',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'notes',
        'status',
    ];

    protected $casts = [
        'work_date'           => 'date',
        'clock_in_at'         => 'datetime',
        'clock_out_at'        => 'datetime',
        'clock_in_latitude'   => 'float',
        'clock_in_longitude'  => 'float',
        'clock_out_latitude'  => 'float',
        'clock_out_longitude' => 'float',
        'status'              => ClockStatus::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class, 'attendance_record_id');
    }
}
