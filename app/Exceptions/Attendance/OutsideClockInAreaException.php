<?php

namespace App\Exceptions\Attendance;

use App\Support\ErrorCode;

class OutsideClockInAreaException extends AttendanceException
{
    public function __construct(string $message = 'You are outside the company clock-in area.')
    {
        parent::__construct(ErrorCode::Forbidden, $message);
    }
}
