<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends BaseModel
{


    protected $fillable = [

        'name',
        'clock_in_time',
        'clock_out_time',
        'grace_period_minutes',
        'is_default',

    ];

    protected $casts = [
        'grace_period_minutes' => 'integer',
        'is_default' => 'boolean',
    ];


    public function employeeWorkSchedules(): HasMany
    {
        return $this->hasMany(EmployeeWorkSchedule::class, 'work_schedule_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'work_schedule_id');
    }
}
