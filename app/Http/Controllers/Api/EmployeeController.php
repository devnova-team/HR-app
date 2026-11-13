<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexEmployeeRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\EmployeeSummaryResource;
use App\Services\EmployeeService;
use App\Support\Pagination;

class EmployeeController extends Controller
{
    public function __construct(protected EmployeeService $service) {}

    public function index(IndexEmployeeRequest $request)
    {
        $employees = $this->service->directory(
            $request->user(),
            $request->validated(),
            Pagination::limit($request)
        );

        return new ApiCollection($employees, EmployeeSummaryResource::class);
    }
}
