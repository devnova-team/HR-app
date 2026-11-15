<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;

class DepartmentController extends Controller
{
    public function __construct(protected DepartmentService $service) {}

    public function index()
    {
        return DepartmentResource::collection($this->service->list());
    }
}
