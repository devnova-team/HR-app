<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceResource extends JsonResource
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
            'category' => new LeaveCategoryResource($this->whenLoaded('leaveCategory')),
            'year' => (int) $this->year,
            'total_days' => (float) $this->total_days,
            'used_days' => (float) $this->used_days,
            'remaining_days' => (float) $this->remaining_days,
        ];
    }
}
