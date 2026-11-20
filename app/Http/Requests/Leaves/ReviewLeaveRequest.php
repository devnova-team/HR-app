<?php

namespace App\Http\Requests\Leaves;

use App\Enums\LeaveStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([LeaveStatus::Approved->value, LeaveStatus::Rejected->value])],
            'rejection_reason' => ['required_if:status,'.LeaveStatus::Rejected->value, 'nullable', 'string', 'max:1000'],
        ];
    }
}
