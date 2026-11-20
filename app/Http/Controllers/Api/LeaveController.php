<?php

namespace App\Http\Controllers\Api;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leaves\LeaveIndexRequest;
use App\Http\Requests\Leaves\ReviewLeaveRequest;
use App\Http\Requests\Leaves\StoreLeaveRequest;
use App\Http\Resources\LeaveBalanceResource;
use App\Http\Resources\LeaveCategoryResource;
use App\Http\Resources\LeaveRequestResource;
use App\Services\Interfaces\LeaveServiceInterface;
use App\Support\ApiPagination;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(
        protected LeaveServiceInterface $service
    ) {}

    public function index(LeaveIndexRequest $request): JsonResponse
    {
        $employee = $request->user();
        $limit = (int) $request->validated('limit', 20);

        $leaves = $this->service->listEmployeeLeaves(
            $employee,
            $request->validated(),
            $limit
        );

        return ApiPagination::respond($leaves, LeaveRequestResource::class);
    }

    public function categories(): JsonResponse
    {
        $categories = $this->service->listActiveCategories();

        return ApiResponse::success(
            LeaveCategoryResource::collection($categories)
        );
    }

    public function balances(Request $request): JsonResponse
    {
        $year = $request->query('year') ? (int) $request->query('year') : null;
        $balances = $this->service->getEmployeeBalances($request->user(), $year);

        return ApiResponse::success(
            LeaveBalanceResource::collection($balances)
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $leave = $this->service->getLeaveDetails($request->user(), $id);

        return ApiResponse::success(new LeaveRequestResource($leave));
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $leave = $this->service->submitLeaveRequest(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::created(new LeaveRequestResource($leave));
    }

    public function review(ReviewLeaveRequest $request, string $id): JsonResponse
    {
        $status = LeaveStatus::from($request->validated('status'));

        $leave = $this->service->reviewLeaveRequest(
            $request->user(),
            $id,
            $status,
            $request->validated('rejection_reason')
        );

        return ApiResponse::success(new LeaveRequestResource($leave));
    }
}
