<?php

namespace App\Repositories;

use App\Exceptions\Attendance\CompanySettingNotConfiguredException;
use App\Models\CompanySetting;
use App\Repositories\Interfaces\CompanySettingRepositoryInterface;

class CompanySettingRepository implements CompanySettingRepositoryInterface
{
    public function get(): CompanySetting
    {
        return CompanySetting::query()->first()
            ?? throw new CompanySettingNotConfiguredException();
    }
}
