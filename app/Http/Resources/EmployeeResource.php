<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

    return[
        'id' => $this->id,
        'employeeId' => $this->employee_id,
        'fullName' => $this->full_name,
        'jobTitle' => $this->position_label,
        'avatarUrl' => $this->avatar_url,
        'isVerified' => $this->is_verified,
        'email' => $this->email,
        'address' => $this->address,
        'dateOfBirth' => $this->date_of_birth?->toIso8601ZuluString(),
    ];
    }


}
