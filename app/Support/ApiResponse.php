<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    public static function success(mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
        ], $status);
    }

    public static function created(mixed $data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
        ], 201);
    }

    // No content response
    public static function noContent(): Response
    {
        return response()->noContent();
    }

    public static function error(ErrorCode $code, string $message, ?array $fields = null): JsonResponse
    {

        $error = [
            'code' => $code->value,
            'message' => $message,
        ];
        if (! empty($fields)) {
            $error['fields'] = $fields;
        }

        return response()->json([
            'error' => $error,
        ], $code->status());  // Use the status code from the ErrorCode enum

    }
}
