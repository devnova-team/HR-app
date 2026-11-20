<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Review = 'review';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
