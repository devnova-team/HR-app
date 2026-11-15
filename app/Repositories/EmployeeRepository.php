<?php

namespace App\Repositories;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRepository
{
    public function paginateDirectory(
        string $excludeId,
        ?string $search,
        ?string $departmentId,
        int $limit
    ): LengthAwarePaginator {
        return Employee::query()
            ->with(['level', 'jobTitle'])
            ->where('is_active', true)
            ->where('id', '!=', $excludeId)
            ->when($departmentId, fn (Builder $query) => $query->where('department_id', $departmentId))
            ->when($search, function (Builder $query, string $term) {
                $like = '%'.addcslashes($term, '\\%_').'%';

                $query->where(function (Builder $inner) use ($like) {
                    $inner->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", [$like]);
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('id')
            ->paginate($limit);
    }
}
