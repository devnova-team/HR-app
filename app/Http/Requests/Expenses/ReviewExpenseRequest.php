<?php

namespace App\Http\Requests\Expenses;

use App\Enums\ExpenseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([ExpenseStatus::Approved->value, ExpenseStatus::Rejected->value])],
            'rejection_reason' => ['required_if:status,'.ExpenseStatus::Rejected->value, 'nullable', 'string', 'max:1000'],
        ];
    }
}
