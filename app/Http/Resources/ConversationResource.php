<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'participants' => $this->whenLoaded('participants', function () {
                return $this->participants->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'employee_id' => $employee->employee_id,
                    ];
                });
            }),

            'latest_message' => $this->whenLoaded(
                'latestMessage',
                function () {
                    return [
                        'id' => $this->latestMessage->id,
                        'sender_id' => $this->latestMessage->sender_id,
                        'type' => $this->latestMessage->type,
                        'body' => $this->latestMessage->body,
                        'media_url' => $this->latestMessage->media_url,
                        'created_at' => $this->latestMessage->created_at,
                    ];
                }
            ),
            'unread_count' => $this->unread_count ?? 0,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}