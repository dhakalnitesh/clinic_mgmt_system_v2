<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visit_id' => ['required', 'exists:visits,id'],
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],

            'chief_complaint' => ['nullable', 'string'],
            'history' => ['nullable', 'string'],
            'examination_notes' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],


            'blood_pressure' => ['nullable'],
            'pulse' => ['nullable'],
            'temperature' => ['nullable'],
            'oxygen' => ['nullable'],
            'height' => ['nullable'],
            'weight' => ['nullable'],

            'prescription' => ['nullable', 'array'],
            'prescription.notes' => ['nullable', 'string'],
            'prescription.items' => ['nullable', 'array'],


            'medicines' => ['nullable', 'array'],
            'medicines.*.medicine_id' => ['nullable'],
            'medicines.*.dosage' => ['nullable'],
            'medicines.*.frequency' => ['nullable'],
            'medicines.*.duration' => ['nullable'],
            'medicines.*.instruction' => ['nullable'],
        ];
    }
}
