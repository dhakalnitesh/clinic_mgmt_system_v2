<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_id'                       => ['nullable', 'integer'],
            'prescription_id'                  => ['nullable', 'exists:prescriptions,id'],
            'sale_date'                        => ['required', 'date'],
            'sale_type'                        => ['required', 'in:counter,prescription,opd,ipd'],
            'pharmacist_id'                    => ['nullable', 'exists:users,id'],
            'discount_type'                    => ['required', 'in:percent,amount'],
            'discount_value'                   => ['required', 'numeric', 'min:0'],
            'payment_mode'                     => ['required', 'in:cash,card,insurance,credit,upi,bank_transfer,mixed'],
            'payment_reference'                => ['nullable', 'string', 'max:100'],
            'paid_amount'                      => ['required', 'numeric', 'min:0'],
            'notes'                            => ['nullable', 'string'],

            'items'                            => ['required', 'array', 'min:1'],
            'items.*.medicine_id'              => ['required', 'exists:medicines,id'],
            'items.*.quantity'                 => ['required', 'integer', 'min:1'],
            'items.*.unit_price'               => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent'         => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_percent'              => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.prescription_item_id'     => ['nullable', 'exists:prescription_items,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'               => 'Cart cannot be empty. Add at least one medicine.',
            'items.*.medicine_id.required' => 'Each item must have a medicine selected.',
            'items.*.quantity.min'         => 'Quantity must be at least 1.',
        ];
    }
}