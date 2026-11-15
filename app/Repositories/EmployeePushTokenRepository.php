<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\EmployeePushToken;
use App\Repositories\Interfaces\EmployeePushTokenRepositoryInterface;

class EmployeePushTokenRepository implements EmployeePushTokenRepositoryInterface
{
    public function store(Employee $employee, array $data): EmployeePushToken
    {
        return EmployeePushToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'employee_id' => $employee->id,
                'platform' => $data['platform'],
            ]
        );
    }

    public function delete(EmployeePushToken $pushToken): bool
    {
        return (bool) $pushToken->delete();
    }
}