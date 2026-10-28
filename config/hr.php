<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HR Application Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration settings for the HR application.
    | You can define various settings related to HR functionalities here.
    |
    */

    'otp_ttl_minutes' => (int) env('OTP_TTL_MINUTES', 10),
    'otp_max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'reset_token_ttl_minutes' => (int) env('RESET_TOKEN_TTL_MINUTES', 10),
    'task_min_assigner_rank' => (int) env('TASK_MIN_ASSIGNER_RANK', 3),
    'access_token_ttl_minutes' => (int) env('ACCESS_TOKEN_TTL_MINUTES', 60),
    'refresh_token_ttl_days' => (int) env('REFRESH_TOKEN_TTL_DAYS', 30),

];
