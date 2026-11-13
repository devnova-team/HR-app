<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\MeetingRepository;
use Illuminate\Database\Eloquent\Collection;

class MeetingService
{
    public function __construct(protected MeetingRepository $repository) {}

    /** "Today" is the company's day (company_settings.timezone), not the server's. */
    public function today(Employee $employee): Collection
    {
        $companyTimezone = $this->repository->companyTimezone() ?: config('app.timezone');
        $storageTimezone = config('app.timezone');

        return $this->repository->forEmployeeBetween(
            $employee,
            now($companyTimezone)->startOfDay()->setTimezone($storageTimezone),
            now($companyTimezone)->endOfDay()->setTimezone($storageTimezone)
        );
    }
}
