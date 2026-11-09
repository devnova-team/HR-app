<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resetToken' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8', 'max:255'],
            'confirmNewPassword' => ['required', 'string', 'same:newPassword'],
        ];
    }
}
