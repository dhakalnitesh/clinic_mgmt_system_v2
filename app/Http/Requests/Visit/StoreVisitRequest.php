<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust if you have authorization logic, e.g., policies
        return true;
    }

    public function rules(): array
    {
        return [
            // Either select an existing patient OR provide new patient details
            'patient_id' => ['nullable', 'exists:patients,id'],

            'new_patient.name' => ['required_without:patient_id', 'string', 'max:255'],
            'new_patient.phone' => ['required_without:patient_id', 'string', 'max:20'],
            'new_patient.address1' => ['nullable', 'string', 'max:255'],

            // Visit fields
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],

            'symptoms' => ['nullable', 'string', 'max:1000'],
            'diagnosis' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // 'status' => ['nullable', Rule::in(['waiting', 'in_progress', 'completed', 'cancelled'])],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.exists' => 'Selected patient does not exist.',
            'new_patient.name.required_without' => 'Patient name is required if not selecting existing patient.',
            'new_patient.phone.required_without' => 'Patient phone is required if not selecting existing patient.',
            'doctor_id.required' => 'Please select a doctor for this visit.',
            'doctor_id.exists' => 'Selected doctor does not exist.',
            'appointment_id.exists' => 'Selected appointment does not exist.',
            'status.in' => 'Invalid status selected.',
        ];
    }
}