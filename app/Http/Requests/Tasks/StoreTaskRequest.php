<?php

namespace App\Http\Requests\Tasks;

use App\Enums\TaskDifficulty;
use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
            ],

            'description' => [
                'required',
                'string',
            ],

            'assigneeIds' => [
                'required',
                'array',
            ],

            'assigneeIds.*' => [
                'required',
                'string',
                'distinct',
                Rule::exists('employees', 'id'),
                Rule::notIn([
                    $this->user()->getAuthIdentifier(),
                ]),
            ],

            'priority' => [
                'required',
                Rule::enum(TaskPriority::class),
            ],

            'difficulty' => [
                'required',
                Rule::enum(TaskDifficulty::class),
            ],

            'dueDate' => [
                'required',
                'date',
            ],

            'attachments' => [
                'sometimes',
                'array',
                'max:3',
            ],

            'attachments.*' => [
                'bail',
                'file',
                'mimetypes:application/pdf,image/jpeg,image/png',
                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail
                ): void {
                    if (
                        $value instanceof UploadedFile
                        && $value->getSize() >= 5 * 1024 * 1024
                    ) {
                        $fail('Each attachment must be smaller than 5MB.');
                    }
                },
            ],
        ];
    }
}
