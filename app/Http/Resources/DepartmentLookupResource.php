<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentLookupResource extends JsonResource
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
            // نستخدم Resource آخر للمسميات الوظيفية أو نرجعها كـ Array مباشر
            'jobTitles' => $this->jobTitles->map(function ($jobTitle) {
                return [
                    'id' => $jobTitle->id,
                    'name' => $jobTitle->name,
                ];
            }),
        ];
    }
}
