<?php

namespace App\Exceptions\Attendance;

use App\Support\ErrorCode;

class AlreadyClockedOutException extends AttendanceException
{
    public function __construct(string $message = 'You have already clocked out today.')
    {
        parent::__construct(ErrorCode::Conflict, $message);
    }
}
