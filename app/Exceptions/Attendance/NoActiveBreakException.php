<?php

namespace App\Exceptions\Attendance;

use App\Support\ErrorCode;

class NoActiveBreakException extends AttendanceException
{
    public function __construct(string $message = 'There is no active break to end.')
    {
        parent::__construct(ErrorCode::Conflict, $message);
    }
}
