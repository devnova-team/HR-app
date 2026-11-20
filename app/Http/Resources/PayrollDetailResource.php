<?php

namespace App\Http\Resources;

use App\Support\ApiDate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PayrollRecord */
class PayrollDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'periodStart' => ApiDate::isoDate($this->period_start),
            'periodEnd' => ApiDate::isoDate($this->period_end),
            'overtimeHours' => $this->overtime_hours_minutes * 60,
            'payPeriodHours' => $this->pay_period_hours_minutes * 60,
            'basicSalary' => round((float) $this->basic_salary, 2),
            'tax' => round((float) $this->tax, 2),
            'reimbursement' => round((float) $this->reimbursement, 2),
            'bonus' => round((float) $this->bonus, 2),
            'overtime' => round((float) $this->overtime_pay, 2),
            'totalSalary' => round((float) $this->total_salary, 2),
            'currency' => $this->currency,
        ];
    }
}
