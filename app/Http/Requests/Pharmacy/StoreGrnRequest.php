<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StoreGrnRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'supplier_id'                       => ['required', 'exists:suppliers,id'],
            'purchase_order_id'                 => ['nullable', 'exists:purchase_orders,id'],
            'received_date'                     => ['required', 'date'],
            'invoice_number'                    => ['nullable', 'string', 'max:80'],
            'invoice_date'                      => ['nullable', 'date'],
            'notes'                             => ['nullable', 'string'],

            'items'                             => ['required', 'array', 'min:1'],
            'items.*.medicine_id'               => ['required', 'exists:medicines,id'],
            'items.*.purchase_order_item_id'    => ['nullable', 'exists:purchase_order_items,id'],
            'items.*.batch_number'              => ['required', 'string', 'max:80'],
            'items.*.manufacturing_date'        => ['nullable', 'date'],
            'items.*.expiry_date'               => ['required', 'date', 'after:today'],
            'items.*.quantity_received'         => ['required', 'integer', 'min:1'],
            'items.*.free_quantity'             => ['required', 'integer', 'min:0'],
            'items.*.unit_price'                => ['required', 'numeric', 'min:0'],
            'items.*.sale_price'                => ['required', 'numeric', 'min:0'],
            'items.*.mrp'                       => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_percent'          => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_percent'               => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                        => 'At least one item is required to create a GRN.',
            'items.*.batch_number.required'         => 'Batch number is required for every item.',
            'items.*.expiry_date.required'          => 'Expiry date is required for every item.',
            'items.*.expiry_date.after'             => 'Expiry date must be a future date.',
            'items.*.quantity_received.min'         => 'Quantity received must be at least 1.',
        ];
    }
}