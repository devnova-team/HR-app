<?php

namespace App\Services;

use App\Repositories\DepartmentRepository;
use Illuminate\Database\Eloquent\Collection;

class DepartmentService
{
    public function __construct(protected DepartmentRepository $repository) {}

    public function list(): Collection
    {
        return $this->repository->getAll();
    }
}
