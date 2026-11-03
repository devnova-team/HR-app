<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'id',
        'name',
        'clock_in_time',
        'clock_out_time',
        'grace_period_minutes',
        'is_default',

    ];

    protected $casts = ['grace_period_minutes',
        'is_default',];


    public function employee_work_schedules()
    {
        return $this->hasMany(EmployeeWorkSchedule::class , 'work_schedule_id');
    }

    public function attendance_records()
    {
        return $this->hasMany(AttendanceRecord::class , 'work_schedule_id');
    }
}
