<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Access is enforced by the role:hr route middleware.
    }

    public function rules(): array
    {
        return [
            'firstName' => ['sometimes', 'string', 'max:100'],
            'lastName' => ['sometimes', 'string', 'max:100'],
            'phoneNumber' => ['sometimes', 'string', 'max:30'],
            'levelId' => ['sometimes', 'uuid', Rule::exists('levels', 'id')],
            // Department and job title travel together so they can never disagree.
            'departmentId' => ['required_with:jobTitleId', 'uuid', Rule::exists('departments', 'id')],
            'jobTitleId' => [
                'required_with:departmentId', 'uuid',
                Rule::exists('job_titles', 'id')->where('department_id', $this->input('departmentId')),
            ],
            'address' => ['sometimes', 'array'],
            'address.countryId' => ['required_with:address', 'uuid', Rule::exists('countries', 'id')],
            'address.governorateId' => [
                'required_with:address', 'uuid',
                Rule::exists('governorates', 'id')->where('country_id', $this->input('address.countryId')),
            ],
            'address.cityId' => [
                'required_with:address', 'uuid',
                Rule::exists('cities', 'id')->where('governorate_id', $this->input('address.governorateId')),
            ],
            'address.fullAddress' => ['required_with:address', 'string', 'max:255'],
            'dateOfBirth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'isActive' => ['sometimes', 'boolean'],
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
