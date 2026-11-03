<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    protected $fillable = [
        'attendance_id',
        'attendance_record_id',
        'started_at',
        'ended_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id');
    }
}
