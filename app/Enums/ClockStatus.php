<?php

namespace App\Enums;

enum ClockStatus: string
{
    case NOT_CLOCKED_IN = 'notClockedIn';
    case CLOCKED_IN     = 'clockedIn';
    case ON_BREAK       = 'onBreak';
    case CLOCKED_OUT    = 'clockedOut';
}
