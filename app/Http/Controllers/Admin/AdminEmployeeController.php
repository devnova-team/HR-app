<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\EmployeeResource;
use App\Services\EmployeeAdminService;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Http\Request;

class AdminEmployeeController extends Controller
{
    public function __construct(protected EmployeeAdminService $service) {}

    public function index(Request $request)
    {
        return new ApiCollection(
            $this->service->list(Pagination::limit($request)),
            EmployeeResource::class
        );
    }

    public function store(StoreEmployeeRequest $request)
    {
        return ApiResponse::created(new EmployeeResource($this->service->create($request->validated())));
    }

    public function update(UpdateEmployeeRequest $request, string $id)
    {
        return ApiResponse::success(new EmployeeResource($this->service->update($id, $request->validated())));
    }
}
