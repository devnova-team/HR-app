<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'category' => new ExpenseCategoryResource($this->whenLoaded('expenseCategory')),
            'expense_category_id' => $this->expense_category_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'transaction_date' => $this->transaction_date?->toISOString(),
            'description' => $this->description,
            'document_url' => $this->document_url,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'reviewed_by' => $this->whenLoaded('reviewedBy', fn () => [
                'id' => $this->reviewedBy->id,
                'name' => $this->reviewedBy->name ?? $this->reviewedBy->first_name.' '.$this->reviewedBy->last_name,
            ]),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'payroll_record_id' => $this->payroll_record_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
