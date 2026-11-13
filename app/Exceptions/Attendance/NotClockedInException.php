<?php

namespace App\Exceptions\Attendance;

use App\Support\ErrorCode;

class NotClockedInException extends AttendanceException
{
    public function __construct(string $message = 'You have not clocked in today.')
    {
        parent::__construct(ErrorCode::Conflict, $message);
    }
}
