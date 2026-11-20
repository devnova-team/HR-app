<?php

namespace App\Http\Requests\Leaves;

use App\Enums\LeaveStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(LeaveStatus::class)],
            'leave_category_id' => ['nullable', 'uuid', 'exists:leave_categories,id'],
            'category_id' => ['nullable', 'uuid', 'exists:leave_categories,id'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
