<?php

namespace App\Http\Requests\Tasks;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'scope' => $this->input('scope', 'mine'),
            'page' => $this->input('page', 1),
            'limit' => $this->input('limit', 20),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'scope' => [
                'required',
                Rule::in(['mine', 'assigned']),
            ],

            'status' => [
                'sometimes',
                'nullable',
                Rule::enum(TaskStatus::class),
            ],

            'page' => [
                'required',
                'integer',
                'min:1',
            ],

            'limit' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
