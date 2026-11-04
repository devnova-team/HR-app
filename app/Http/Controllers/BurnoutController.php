<?php

namespace App\Http\Controllers;

use App\Services\BurnoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BurnoutController extends Controller
{
    public function __construct(private readonly BurnoutService $burnout) {}

    /** GET /tasks/burnout */
    public function stats(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->burnout->stats($request->user())]);
    }

    /** GET /tasks/burnout/details */
    public function details(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->burnout->details($request->user())]);
    }
}
