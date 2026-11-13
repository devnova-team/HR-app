<?php

namespace App\Http\Resources;

use App\Support\ApiDate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Employee */
class PersonalDataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'dateOfBirth' => ApiDate::isoDate($this->date_of_birth),
            'avatarUrl' => $this->avatar_url,

            'position' => [
                'level' => $this->level?->name,
                'department' => $this->department?->name,
                'jobTitle' => $this->jobTitle?->name,
            ],

            'address' => [
                'country' => $this->country?->name,
                'governorate' => $this->governorate?->name,
                'city' => $this->city?->name,
                'fullAddress' => $this->full_address,
            ],
        ];
    }
}