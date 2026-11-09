<?php

namespace App\Exceptions\Attendance;

use App\Support\ErrorCode;

class WorkScheduleNotFoundException extends AttendanceException
{
    public function __construct(string $message = 'No work schedule is assigned to you.')
    {
        parent::__construct(ErrorCode::NotFound, $message);
    }
}
