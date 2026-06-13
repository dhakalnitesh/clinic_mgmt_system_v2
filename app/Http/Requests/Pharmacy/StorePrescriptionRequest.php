<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_id'                     => ['required', 'integer', 'exists:patients,id'],
            'doctor_id'                      => ['required', 'integer', 'exists:doctors,id'],
            'encounter_id'                   => ['nullable', 'integer', 'exists:encounters,id'],
            'prescription_date'              => ['required', 'string'],
            'notes'                          => ['nullable', 'string'],

            'items'                          => ['required', 'array', 'min:1'],
            'items.*.medicine_id'            => ['nullable', 'exists:medicines,id'],
            'items.*.medicine_name'          => ['required_without:items.*.medicine_id', 'string', 'max:255'],
            'items.*.generic_id'             => ['nullable', 'exists:generics,id'],
            'items.*.dosage_instruction'     => ['nullable', 'string', 'max:200'],
            'items.*.frequency'              => ['nullable', 'string'],
            'items.*.duration_days'          => ['nullable', 'integer', 'min:1'],
            'items.*.route'                  => ['nullable', 'string', 'max:50'],
            'items.*.quantity_prescribed'    => ['required', 'integer', 'min:1'],
            'items.*.is_substitutable'       => ['boolean'],
            'items.*.instructions'           => ['nullable', 'string'],
        ];
    }
}