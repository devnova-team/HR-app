<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeePushTokenRequest;
use App\Services\EmployeePushTokenService;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Auth;

class EmployeePushTokenController extends Controller
{
    public function __construct(
        protected EmployeePushTokenService $service
    ) {}

    public function store(StoreEmployeePushTokenRequest $request)
    {
        $employee = Auth::user();
        $pushToken = $this->service->registerToken(
            $employee,
            $request->validated()
        );

        return ApiResponse::success($pushToken);
    }
}