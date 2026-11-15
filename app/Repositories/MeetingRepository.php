<?php

namespace App\Repositories;

use App\Models\CompanySetting;
use App\Models\Employee;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class MeetingRepository
{
    /** Meetings of the employee that overlap the [from, to] window. */
    public function forEmployeeBetween(Employee $employee, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $employee->meetings()
            ->where('meetings.starts_at', '<=', $to)
            ->where('meetings.ends_at', '>=', $from)
            ->with(['participants.level', 'participants.jobTitle'])
            ->orderBy('meetings.starts_at')
            ->get();
    }

    public function companyTimezone(): ?string
    {
        return CompanySetting::query()->value('timezone');
    }
}
