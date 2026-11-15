<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeService
{
    public function __construct(protected EmployeeRepository $repository) {}

    public function directory(Employee $viewer, array $filters, int $limit): LengthAwarePaginator
    {
        return $this->repository->paginateDirectory(
            $viewer->id,
            $filters['search'] ?? null,
            $filters['departmentId'] ?? null,
            $limit
        );
    }
}
