<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'supplier_id'                    => ['required', 'exists:suppliers,id'],
            'order_date'                     => ['required', 'date'],
            'expected_delivery_date'         => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes'                          => ['nullable', 'string'],

            'items'                          => ['required', 'array', 'min:1'],
            'items.*.medicine_id'            => ['required', 'exists:medicines,id'],
            'items.*.quantity_ordered'       => ['required', 'integer', 'min:1'],
            'items.*.unit_price'             => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent'       => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_percent'            => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'Please add at least one medicine to the purchase order.',
            'items.min'                   => 'Purchase order must have at least one item.',
            'items.*.medicine_id.required'=> 'Please select a medicine for each line item.',
            'items.*.quantity_ordered.min'=> 'Quantity must be at least 1.',
        ];
    }
}