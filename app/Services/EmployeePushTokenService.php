<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeePushToken;
use App\Repositories\Interfaces\EmployeePushTokenRepositoryInterface;

class EmployeePushTokenService
{
    public function __construct(
        protected EmployeePushTokenRepositoryInterface $repo
    ) {}

    public function registerToken(Employee $employee, array $data): EmployeePushToken
    {
        return $this->repo->store($employee, $data);
    }

    public function removeToken(EmployeePushToken $pushToken): bool
    {
        return $this->repo->delete($pushToken);
    }
}