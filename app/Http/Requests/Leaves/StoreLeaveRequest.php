<?php

namespace App\Http\Requests\Leaves;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_category_id' => ['required_without:category_id', 'nullable', 'uuid', 'exists:leave_categories,id'],
            'category_id' => ['required_without:leave_category_id', 'nullable', 'uuid', 'exists:leave_categories,id'],
            'task_delegate_id' => ['nullable', 'uuid', 'exists:employees,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days_count' => ['sometimes', 'nullable', 'numeric', 'min:0.5'],
            'description' => ['required', 'string', 'max:1000'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:30'],
        ];
    }
}
