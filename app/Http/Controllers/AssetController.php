<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssetResource;
use App\Models\EmployeeAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    
    public function __invoke(Request $request): JsonResponse
    {
        $items = EmployeeAsset::query()
            ->where('employee_id', $request->user()->id)
            ->current()
            ->with('asset')
            ->orderByDesc('assigned_at')
            ->get();

        return response()->json(['data' => AssetResource::collection($items)->resolve()]);
    }
}
