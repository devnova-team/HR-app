<?php

namespace App\Repositories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class DepartmentRepository
{
    public function getAll(): Collection
    {
        return Department::orderBy('name')->get();
    }
}
