<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Employees & Organization Configuration
    |--------------------------------------------------------------------------
    |
    | Constants used by the employees, roles and seeding code. Read them with
    | config('employees.*') and never call env() outside the config files.
    |
    */

    'employee_id' => [
        'prefix' => 'EMP-',
        'padding' => 4,
    ],

    'initial_password_length' => (int) env('EMPLOYEE_INITIAL_PASSWORD_LENGTH', 12),

    /*
    | Permission names. These are the only permissions the application checks.
    */
    'permissions' => [
        'employees.manage',
        'expenses.review',
        'leaves.review',
    ],

    /*
    | Role name => permissions granted to that role.
    */
    'roles' => [
        'employee' => [],
        'hr' => [
            'employees.manage',
            'expenses.review',
            'leaves.review',
        ],
    ],

    /*
    | Levels. The rank drives the task hierarchy (higher means more senior).
    */
    'levels' => [
        1 => 'Intern',
        2 => 'Junior',
        3 => 'Mid-level',
        4 => 'Senior',
        5 => 'Team Lead',
    ],

    /*
    | First HR account, created by HrUserSeeder. Without it nobody can call
    | the /admin endpoints. The password has no default on purpose.
    */
    'hr_account' => [
        'first_name' => env('HR_SEED_FIRST_NAME', 'HR'),
        'last_name' => env('HR_SEED_LAST_NAME', 'Admin'),
        'email' => env('HR_SEED_EMAIL', 'hr@example.com'),
        'phone_number' => env('HR_SEED_PHONE', '0000000000'),
        'full_address' => env('HR_SEED_ADDRESS', 'Head office'),
        'password' => env('HR_SEED_PASSWORD'),
    ],

];
