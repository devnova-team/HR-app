<?php

namespace App\Exceptions\Attendance;

use App\Support\ErrorCode;

class AlreadyClockedInException extends AttendanceException
{
    public function __construct(string $message = 'You have already clocked in today.')
    {
        parent::__construct(ErrorCode::Conflict, $message);
    }
}
