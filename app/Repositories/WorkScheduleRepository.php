<?php

namespace App\Repositories;

use App\Models\EmployeeWorkSchedule;
use App\Models\WorkSchedule;
use App\Repositories\Interfaces\WorkScheduleRepositoryInterface;

class WorkScheduleRepository implements WorkScheduleRepositoryInterface
{
    public function findEffectiveForEmployee(string $employeeId, string $date): ?WorkSchedule
    {
        $assignment = EmployeeWorkSchedule::with('workSchedule')
            ->where('employee_id', $employeeId)
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();

        return $assignment?->workSchedule;
    }

    public function findDefault(): ?WorkSchedule
    {
        return WorkSchedule::where('is_default', true)->first();
    }
}
