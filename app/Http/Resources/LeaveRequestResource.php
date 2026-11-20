<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'category' => new LeaveCategoryResource($this->whenLoaded('leaveCategory')),
            'leave_category_id' => $this->leave_category_id,
            'task_delegate' => $this->whenLoaded('taskDelegate', fn () => [
                'id' => $this->taskDelegate->id,
                'name' => $this->taskDelegate->name ?? $this->taskDelegate->first_name.' '.$this->taskDelegate->last_name,
            ]),
            'task_delegate_id' => $this->task_delegate_id,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'days_count' => (float) $this->days_count,
            'total_days' => (float) $this->days_count,
            'description' => $this->description,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'reviewed_by' => $this->whenLoaded('reviewedBy', fn () => [
                'id' => $this->reviewedBy->id,
                'name' => $this->reviewedBy->name ?? $this->reviewedBy->first_name.' '.$this->reviewedBy->last_name,
            ]),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
