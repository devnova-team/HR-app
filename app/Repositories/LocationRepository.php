<?php

namespace App\Repositories;

use App\Models\Country;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Collection;

class LocationRepository
{
    public function getCountries(): Collection
    {
        return Country::orderBy('name')->get();
    }

    public function getGovernoratesByCountry(string $countryId): Collection
    {
        return Country::findOrFail($countryId)
            ->governorates()
            ->orderBy('name')
            ->get();
    }

    public function getCitiesByGovernorate(string $governorateId): Collection
    {
        return Governorate::findOrFail($governorateId)
            ->cities()
            ->orderBy('name')
            ->get();
    }
}
