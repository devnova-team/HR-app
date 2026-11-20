<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveCategoryResource extends JsonResource
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
            'name' => $this->name,
            'default_days' => (float) $this->default_days,
            'is_paid' => (bool) $this->is_paid,
            'requires_balance' => (bool) $this->requires_balance,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
