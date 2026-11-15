<?php

namespace App\Exceptions\Attendance;

use App\Support\ErrorCode;

class BreakAlreadyStartedException extends AttendanceException
{
    public function __construct(string $message = 'A break is already in progress.')
    {
        parent::__construct(ErrorCode::Conflict, $message);
    }
}
