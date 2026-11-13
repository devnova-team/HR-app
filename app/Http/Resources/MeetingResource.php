<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    /**
     * Requires participants.level and participants.jobTitle to be loaded.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'startTime' => $this->starts_at?->toIso8601ZuluString(),
            'endTime' => $this->ends_at?->toIso8601ZuluString(),
            'meetingLink' => $this->meeting_url,
            'participants' => EmployeeSummaryResource::collection($this->whenLoaded('participants')),
        ];
    }
}
