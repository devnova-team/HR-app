<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Services\LocationService;

class LocationController extends Controller
{
    public function __construct(protected LocationService $service) {}

    public function countries()
    {
        return LocationResource::collection($this->service->countries());
    }

    public function governorates(string $countryId)
    {
        return LocationResource::collection($this->service->governorates($countryId));
    }

    public function cities(string $governorateId)
    {
        return LocationResource::collection($this->service->cities($governorateId));
    }
}
