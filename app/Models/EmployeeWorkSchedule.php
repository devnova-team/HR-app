<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeWorkSchedule extends Model
{

  

    protected $fillable = [
        'employee_id',
        'work_schedule_id',
        'effective_from',
        'effective_to',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function work_schedules()
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }
}
