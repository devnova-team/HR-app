<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'in:text,image,voice,document',
            ],

            'body' => [
                'required_if:type,text',
                'nullable',
                'string',
            ],

            'media_url' => [
                'required_if:type,image,voice,document',
                'nullable',
                'string',
                'max:2048',
            ],

            'file_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'duration_seconds' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}