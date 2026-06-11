<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $doctorId = $this->doctor?->id ?? null;

        return [
            'name' => 'required|string|max:255',
            'nmc_number' => 'required|string|max:50|unique:doctors,nmc_number,' . $doctorId,
            'specialization' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'fee' => 'nullable|numeric|min:0',
            'availability_schedule' => 'nullable|array',
            // Each item in availability_schedule is optional
            'availability_schedule.*.day' => 'nullable|string',
            'availability_schedule.*.time' => 'nullable|string',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Doctor name is required.',
            'nmc_number.required' => 'NMC Number is required.',
            'nmc_number.unique' => 'This NMC Number is already registered.',
            'phone.required' => 'Phone number is required.',
            'photo.image' => 'Uploaded file must be an image.',
            'fee.numeric' => 'Fee must be a valid number.',
            'availability_schedule.array' => 'Availability schedule format is invalid.',
        ];
    }
}