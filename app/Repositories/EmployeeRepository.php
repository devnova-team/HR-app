<?php

namespace App\Repositories;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EmployeeRepository
{
    /** Relations read by EmployeeResource (strict mode forbids lazy loading). */
    private const FULL_RELATIONS = ['level', 'jobTitle', 'country', 'governorate', 'city'];

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

    public function paginateForAdmin(int $limit): LengthAwarePaginator
    {
        return Employee::query()
            ->with(self::FULL_RELATIONS)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('id')
            ->paginate($limit);
    }

    public function findProfile(string $id): Employee
    {
        return Employee::with(['level', 'jobTitle', 'department'])->findOrFail($id);
    }

    public function findWithRelations(string $id): Employee
    {
        return Employee::with(self::FULL_RELATIONS)->findOrFail($id);
    }

    public function emailExists(string $email): bool
    {
        return Employee::where('email', $email)->exists();
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    /** Must run inside a DB transaction: lockForUpdate holds the last row until commit. */
    public function nextEmployeeId(): string
    {
        $prefix = config('employees.employee_id.prefix');
        $padding = (int) config('employees.employee_id.padding');

        $last = Employee::query()
            ->where('employee_id', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING(employee_id, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
            ->lockForUpdate()
            ->value('employee_id');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, $padding, '0', STR_PAD_LEFT);
    }

    public function findOrFail(string $id): Employee
    {
        return Employee::findOrFail($id);
    }

    public function findManyByIds(array $ids): Collection
    {
        return Employee::whereIn('id', $ids)->get();
    }
}
