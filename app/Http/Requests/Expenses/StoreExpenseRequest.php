<?php

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => ['required_without:category_id', 'nullable', 'uuid', 'exists:expense_categories,id'],
            'category_id' => ['required_without:expense_category_id', 'nullable', 'uuid', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'document' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'document_url' => ['nullable', 'string', 'max:500'],
        ];
    }
}
