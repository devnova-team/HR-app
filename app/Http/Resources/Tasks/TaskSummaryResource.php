<?php

namespace App\Http\Resources\Tasks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'toDoCount' => (int) ($this->resource['toDoCount'] ?? 0),
            'inProgressCount' => (int) ($this->resource['inProgressCount'] ?? 0),
            'doneCount' => (int) ($this->resource['doneCount'] ?? 0),
        ];
    }
}
