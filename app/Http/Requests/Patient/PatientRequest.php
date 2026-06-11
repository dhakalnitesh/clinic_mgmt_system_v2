<?php

namespace App\Http\Requests\Patient;
use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Set to true if all authenticated users can create/update patients.
        // Otherwise implement your permission logic here.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'digits:10', 'regex:/^[0-9]+$/'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'gender' => ['nullable', 'string', 'in:Male,Female,Other'],
            'citizenship_type' => ['nullable', 'string', 'in:nepali,foreign'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'municipal_id' => ['nullable', 'exists:municipals,id'],
            'address1' => ['nullable', 'string', 'max:500'],
            'address2' => ['nullable', 'string', 'max:500'],
            'foreign_address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Optional: Custom messages for validation
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The patient name is required.',
            'phone.required' => 'The phone number is required.',
            'phone.digits' => 'The phone number must be exactly 10 digits.',
            'phone.regex' => 'The phone number must contain only numbers.',
            'age.integer' => 'The age must be a number.',
            'gender.in' => 'The gender must be Male, Female, or Other.',
        ];
    }
}