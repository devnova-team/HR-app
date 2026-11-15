<?php

namespace App\Services;

use App\Models\Employee;

/**
 * Fields HR may leave empty at creation (nullable in `employees`).
 * Used by POST /auth/login (missingFields) so the app can offer "Complete profile".
 */
class ProfileCompletionService
{
    /** @return list<string> contract field names, e.g. ["dateOfBirth", "avatarUrl"] */
    public function missingFields(Employee $employee): array
    {
        $missing = [];

        if (empty($employee->date_of_birth)) {
            $missing[] = 'dateOfBirth';
        }
        if (empty($employee->avatar_url)) {
            $missing[] = 'avatarUrl';
        }

        return $missing;
    }
}
