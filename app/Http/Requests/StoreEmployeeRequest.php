<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Access is enforced by the role:hr route middleware.
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            // No "unique" rule on purpose: a duplicate email must return 409, not 400.
            'email' => ['required', 'email:rfc', 'max:255'],
            'phoneNumber' => ['required', 'string', 'max:30'],
            'levelId' => ['required', 'uuid', Rule::exists('levels', 'id')],
            'departmentId' => ['required', 'uuid', Rule::exists('departments', 'id')],
            'jobTitleId' => [
                'required', 'uuid',
                Rule::exists('job_titles', 'id')->where('department_id', $this->input('departmentId')),
            ],
            'address' => ['required', 'array'],
            'address.countryId' => ['required', 'uuid', Rule::exists('countries', 'id')],
            'address.governorateId' => [
                'required', 'uuid',
                Rule::exists('governorates', 'id')->where('country_id', $this->input('address.countryId')),
            ],
            'address.cityId' => [
                'required', 'uuid',
                Rule::exists('cities', 'id')->where('governorate_id', $this->input('address.governorateId')),
            ],
            'address.fullAddress' => ['required', 'string', 'max:255'],
            'dateOfBirth' => ['nullable', 'date', 'before:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'jobTitleId.exists' => 'The job title does not belong to the selected department.',
            'address.governorateId.exists' => 'The governorate does not belong to the selected country.',
            'address.cityId.exists' => 'The city does not belong to the selected governorate.',
        ];
    }
}
