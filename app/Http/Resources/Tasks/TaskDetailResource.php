<?php

namespace App\Http\Resources\Tasks;

use App\Http\Resources\EmployeeSummaryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDetailResource extends JsonResource
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

            'description' => $this->description,

            'status' => $this->status->value,

            'priority' => $this->priority->value,

            'difficulty' => $this->difficulty->value,

            'progress' => (float) ($this->progress / 100),

            'createdAt' => $this->created_at->toIso8601ZuluString(),

            'mediaUrls' => $this->attachments
                ->map(fn ($attachment) => $attachment->signed_url)
                ->values(),

            'assignees' => EmployeeSummaryResource::collection(
                $this->assignees
            ),

            'assignedBy' => new EmployeeSummaryResource(
                $this->assignedBy
            ),

            'completion' => $this->when(
                $this->status->value === 'done'
                    && $this->assigned_by_id === $request->user()->id,
                function () {
                    $completion = [
                        'summary' => $this->completion_summary,
                    ];

                    if ($this->completion_notes !== null) {
                        $completion['notes'] = $this->completion_notes;
                    }

                    $completion['completedAt'] = $this->completed_at->toIso8601ZuluString();

                    return $completion;
                }
            ),
        ];
    }
}
