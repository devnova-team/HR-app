<?php

namespace App\Models;

class CompanySetting extends BaseModel
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'latitude',
        'longitude',
        'allowed_radius_meters',
        'timezone',
        'pay_period_start_day',
        'leave_year_start_month',
    ];

    protected $casts = [
        'latitude' => 'float:7',
        'longitude' => 'float:7',
        'allowed_radius_meters' => 'float:2',
    ];
}
