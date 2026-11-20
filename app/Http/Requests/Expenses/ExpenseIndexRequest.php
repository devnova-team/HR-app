<?php

namespace App\Http\Requests\Expenses;

use App\Enums\ExpenseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ExpenseStatus::class)],
            'category_id' => ['nullable', 'uuid', 'exists:expense_categories,id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
