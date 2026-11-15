<?php

namespace App\Services;

use App\Repositories\LocationRepository;
use Illuminate\Database\Eloquent\Collection;

class LocationService
{
    public function __construct(protected LocationRepository $repository) {}

    public function countries(): Collection
    {
        return $this->repository->getCountries();
    }

    public function governorates(string $countryId): Collection
    {
        return $this->repository->getGovernoratesByCountry($countryId);
    }

    public function cities(string $governorateId): Collection
    {
        return $this->repository->getCitiesByGovernorate($governorateId);
    }
}
