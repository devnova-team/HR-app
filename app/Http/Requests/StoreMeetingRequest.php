<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Access is enforced by the role:hr route middleware.
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'startTime' => ['required', 'date'],
            'endTime' => ['required', 'date', 'after:startTime'],
            'meetingLink' => ['required', 'url', 'max:255'],
            'participantIds' => ['required', 'array', 'min:1'],
            'participantIds.*' => ['uuid', 'distinct', Rule::exists('employees', 'id')],
        ];
    }
}
