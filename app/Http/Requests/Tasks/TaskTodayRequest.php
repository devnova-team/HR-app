<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class TaskTodayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the request data before validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has("limit")) {
            $this->merge([
                "limit" => 20,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            "limit" => ["integer", "min:1", "max:100"],
        ];
    }
}
