<?php

namespace App\Repositories\Interfaces;

use App\Models\CompanySetting;

interface CompanySettingRepositoryInterface
{
    /**
     * The single company settings row.
     *
     * @throws \App\Exceptions\Attendance\CompanySettingNotConfiguredException when the row is missing
     */
    public function get(): CompanySetting;
}
