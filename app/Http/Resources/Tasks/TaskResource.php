<?php

namespace App\Http\Resources\Tasks;

use App\Http\Resources\EmployeeSummaryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'title' => $this->title,

            'status' => $this->status->value,

            'priority' => $this->priority->value,

            'progress' => (float) ($this->progress / 100),

            'dueDate' => $this->due_date->toISOString(),

            'commentsCount' => $this->comments_count,

            'assignees' => EmployeeSummaryResource::collection(
                $this->assignees
            ),
        ];
    }
}
