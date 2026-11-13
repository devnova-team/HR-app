<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'departmentId' => ['nullable', 'uuid'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
