<?php

namespace App\Repositories\Interfaces;

use App\Models\WorkSchedule;

interface WorkScheduleRepositoryInterface
{
    /**
     * The schedule assigned to the employee that is in effect on $date (Y-m-d).
     * Returns null when the employee has no assignment for that date.
     */
    public function findEffectiveForEmployee(string $employeeId, string $date): ?WorkSchedule;

    /** The schedule marked as default (is_default = true), if any. */
    public function findDefault(): ?WorkSchedule;
}
