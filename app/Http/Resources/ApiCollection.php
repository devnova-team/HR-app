<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiCollection extends ResourceCollection
{
    // The $collects property specifies the resource class that this collection will use for individual items.
    public function __construct($resource, string $collects)
    {
        $this->collects = $collects;
        parent::__construct($resource);
    }

    // paginationInformation method to provide pagination details in the response
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'meta' => [
                'page' => $paginated['current_page'],
                'limit' => $paginated['per_page'],
                'total' => $paginated['total'],
                'total_pages' => $paginated['last_page'],
            ],
        ];

    }
}
