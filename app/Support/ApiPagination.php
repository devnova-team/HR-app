<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Local helper for { data: [...], meta: { page, limit, total, totalPages } }.
 * Swap for Kareem's shared Pagination Resource once it is merged.
 */
class ApiPagination
{
    public static function limit(Request $request, int $default = 20, int $max = 100): int
    {
        return min(max((int) $request->query('limit', $default), 1), $max);
    }

    public static function respond(LengthAwarePaginator $paginator, string $resourceClass): JsonResponse
    {
        return response()->json([
            'data' => $resourceClass::collection($paginator->getCollection())->resolve(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }
}
