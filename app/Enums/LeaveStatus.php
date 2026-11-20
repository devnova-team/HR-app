<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case Review = 'review';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
