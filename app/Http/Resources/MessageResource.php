<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender' => $this->whenLoaded('sender', function () {
                return [
                    'id' => $this->sender->id,
                    'first_name' => $this->sender->first_name,
                    'last_name' => $this->sender->last_name,
                    'employee_id' => $this->sender->employee_id,
                ];
            }),
            'type' => $this->type,
            'body' => $this->body,
            'media_url' => $this->media_url,
            'file_name' => $this->file_name,
            'duration_seconds' => $this->duration_seconds,
            'created_at' => $this->created_at,
        ];
    }
}