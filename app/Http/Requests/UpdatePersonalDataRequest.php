<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Only these four fields are editable by the employee. Anything else in the body
 * (position, country, governorate, city...) is ignored because we only use validated().
 */
class UpdatePersonalDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstName' => ['sometimes', 'string', 'max:100'],
            'lastName' => ['sometimes', 'string', 'max:100'],
            'dateOfBirth' => ['sometimes', 'date', 'before:today', 'after:1900-01-01'],
            'fullAddress' => ['sometimes', 'string', 'max:500'],
        ];
    }

    /** @return array<string, mixed> column => value */
    public function attributesToUpdate(): array
    {
        $data = $this->validated();
        $out = [];

        if (array_key_exists('firstName', $data)) {
            $out['first_name'] = trim($data['firstName']);
        }
        if (array_key_exists('lastName', $data)) {
            $out['last_name'] = trim($data['lastName']);
        }
        if (array_key_exists('dateOfBirth', $data)) {
            $out['date_of_birth'] = \Carbon\Carbon::parse($data['dateOfBirth'])->utc()->toDateString();
        }
        if (array_key_exists('fullAddress', $data)) {
            $out['full_address'] = trim($data['fullAddress']);
        }

        return $out;
    }
}
