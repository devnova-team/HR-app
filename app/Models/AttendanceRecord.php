<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'work_schedule_id',
        'work_date',
        'clock_in_time',
        'clock_out_time',
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
        'clock_in_time'       => 'datetime',
        'clock_out_time'      => 'datetime',
        'clock_in_latitude'   => 'float',
        'clock_in_longitude'  => 'float',
        'clock_out_latitude'  => 'float',
        'clock_out_longitude' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class, 'attendance_record_id');
    }
}
