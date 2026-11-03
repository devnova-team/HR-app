<?php


namespace App\Enums;


enum AttendanceStatus: string
{
    case NOT_STARTED = 'not_started';
    case WORKING = 'working';
    case ON_BREAK = 'on_break';
    case COMPLETED = 'completed';
}
