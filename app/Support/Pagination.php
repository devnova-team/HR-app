<?php

namespace App\Support;

use Illuminate\Http\Request;

// This class provides pagination-related utilities for handling API requests.
class Pagination
{
    public const DEFAULT_LIMIT = 20;

    public const MAX_LIMIT = 100;

    public static function limit(Request $request): int
    {

        $limit = (int) $request->query('limit', self::DEFAULT_LIMIT);

        return $limit < 1 ? self::DEFAULT_LIMIT : min($limit, self::MAX_LIMIT);
    }
}
