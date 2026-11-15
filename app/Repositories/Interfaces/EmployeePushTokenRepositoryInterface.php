<?php

namespace App\Repositories\Interfaces;

use App\Models\Employee;
use App\Models\EmployeePushToken;

interface EmployeePushTokenRepositoryInterface
{
    public function store(Employee $employee, array $data): EmployeePushToken;

    public function delete(EmployeePushToken $pushToken): bool;
}