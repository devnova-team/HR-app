<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentLookupResource;
use App\Http\Resources\LevelResource;
use App\Services\LookupService;
use App\Support\ApiResponse; //

class LookupController extends Controller
{
    public function __construct(
        protected LookupService $lookupService
    ) {}

    public function positions()
    {
        $data = $this->lookupService->getPositionsLookup();

        $response = [
            'levels' => LevelResource::collection($data['levels']),
            'departments' => DepartmentLookupResource::collection($data['departments']),
        ];

        return ApiResponse::success($response, 200, 'Positions lookup retrieved successfully');
    }
}
