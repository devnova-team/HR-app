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
            'toDoCount' => (int) $this->toDoCount,

            'inProgressCount' => (int) $this->inProgressCount,

            'doneCount' => (int) $this->doneCount,
        ];
    }
}
