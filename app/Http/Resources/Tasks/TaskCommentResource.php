<?php

namespace App\Http\Resources\Tasks;

use App\Http\Resources\EmployeeSummaryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCommentResource extends JsonResource
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

            'author' => new EmployeeSummaryResource(
                $this->employee
            ),

            'text' => $this->body,

            'createdAt' => $this->created_at->toISOString(),
        ];
    }
}
