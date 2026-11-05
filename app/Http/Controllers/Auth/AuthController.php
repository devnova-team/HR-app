<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    // Inject the AuthService into the controller
    public function __construct(private AuthService $auth) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login($request->validated('employeeId'), $request->validated('password'));

        return ApiResponse::success([
            'accessToken' => $result['access_token'],
            'refreshToken' => $result['refresh_token'],
            'isFirstLogin' => $result['is_first_login'],
            'role' => $result['role'],
            'missingFields' => $result['missingFields'],
            'employee' => (new EmployeeResource($result['employee']))->resolve(), // Return the employee data as an array instead of a resource
        ]);

    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->auth->refreshAccessTokens($request->validated('refreshToken')));
    }

    public function logout(RefreshTokenRequest $request): Response
    {
        $this->auth->logout(auth()->user(), $request->validated('refreshToken'));

        return ApiResponse::noContent();
    }
}
