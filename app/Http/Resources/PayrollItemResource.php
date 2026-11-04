<?php

namespace App\Http\Resources;

use App\Support\ApiDate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PayrollRecord */
class PayrollItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'month' => $this->period_start->format('Y-m'),
            'totalHours' => $this->total_hours_minutes * 60,
            'receivedAmount' => round((float) $this->total_salary, 2),
            'currency' => $this->currency,
            'paidOn' => ApiDate::iso($this->paid_on),
        ];
    }
}
