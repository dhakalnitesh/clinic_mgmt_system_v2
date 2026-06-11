<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:200'],
            'contact_person'       => ['nullable', 'string', 'max:100'],
            'phone'                => ['nullable', 'string', 'max:20'],
            'alternate_phone'      => ['nullable', 'string', 'max:20'],
            'email'                => ['nullable', 'email', 'max:100'],
            'address'              => ['nullable', 'string'],
            'city'                 => ['nullable', 'string', 'max:80'],
            'state'                => ['nullable', 'string', 'max:80'],
            'country'              => ['nullable', 'string', 'max:80'],
            'postal_code'          => ['nullable', 'string', 'max:20'],
            'drug_license_no'      => ['nullable', 'string', 'max:100'],
            'drug_license_expiry'  => ['nullable', 'date'],
            'pan_vat_no'           => ['nullable', 'string', 'max:50'],
            'credit_days'          => ['required', 'integer', 'min:0', 'max:365'],
            'credit_limit'         => ['required', 'numeric', 'min:0'],
            'opening_balance'      => ['required', 'numeric'],
            'is_active'            => ['boolean'],
            'notes'                => ['nullable', 'string'],
        ];
    }
}