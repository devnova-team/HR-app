<?php

namespace App\Exceptions\Attendance;

use App\Support\ErrorCode;

/**
 * A configuration problem (no company_settings row), not a user mistake.
 * It is a server error, so it is still written to the log.
 */
class CompanySettingNotConfiguredException extends AttendanceException
{
    public function __construct(string $message = 'Company settings are not configured.')
    {
        parent::__construct(ErrorCode::ServerError, $message);
    }

    public function report(): bool
    {
        // false = use Laravel's default reporting (log it).
        return false;
    }
}
