<?php

namespace App\Services;

use App\Mail\EmployeeCredentialsMail;
use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class EmployeeAdminService
{
    private const FIELDS = [
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'email' => 'email',
        'phoneNumber' => 'phone_number',
        'levelId' => 'level_id',
        'departmentId' => 'department_id',
        'jobTitleId' => 'job_title_id',
        'dateOfBirth' => 'date_of_birth',
        'isActive' => 'is_active',
    ];

    private const ADDRESS_FIELDS = [
        'countryId' => 'country_id',
        'governorateId' => 'governorate_id',
        'cityId' => 'city_id',
        'fullAddress' => 'full_address',
    ];

    public function __construct(protected EmployeeRepository $repository) {}

    public function list(int $limit): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($limit);
    }

    public function create(array $data): Employee
    {
        if ($this->repository->emailExists($data['email'])) {
            throw new ConflictHttpException('Email is already in use.');
        }

        $password = Str::password((int) config('hr.initial_password_length', 12), symbols: false);

        try {
            $created = DB::transaction(function () use ($data, $password) {
                $employee = $this->repository->create([
                    ...$this->toColumns($data),
                    'employee_id' => $this->repository->nextEmployeeId(),
                    'password' => $password, // hashed by the Employee cast
                ]);

                $employee->assignRole('employee');

                return $employee;
            });
        } catch (UniqueConstraintViolationException) {
            // Lost a race with another request (same email or same generated id).
            throw new ConflictHttpException('Employee already exists.');
        }

        // Reload: a fresh model lacks DB defaults (is_verified...) and strict mode would throw.
        $employee = $this->repository->findWithRelations($created->id);

        // After the commit, so credentials are never mailed for a rolled-back employee.
        Mail::to($employee->email)->queue(
            new EmployeeCredentialsMail($employee->first_name, $employee->employee_id, $password)
        );

        return $employee;
    }

    public function update(string $id, array $data): Employee
    {
        $employee = $this->repository->findOrFail($id);
        $employee->update($this->toColumns($data));

        return $this->repository->findWithRelations($employee->id);
    }

    /** Maps the camelCase request keys to DB columns (only the keys that were sent). */
    private function toColumns(array $data): array
    {
        $columns = [];

        foreach (self::FIELDS as $key => $column) {
            if (array_key_exists($key, $data)) {
                $columns[$column] = $data[$key];
            }
        }

        foreach (self::ADDRESS_FIELDS as $key => $column) {
            if (array_key_exists($key, $data['address'] ?? [])) {
                $columns[$column] = $data['address'][$key];
            }
        }

        return $columns;
    }
}
