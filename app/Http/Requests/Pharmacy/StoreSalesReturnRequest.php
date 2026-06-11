<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesReturnRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sale_id'                             => ['required', 'exists:sales,id'],
            'return_date'                         => ['required', 'date'],
            'reason'                              => ['required', 'in:wrong_medicine,wrong_quantity,adverse_reaction,prescription_changed,patient_refused,duplicate_sale,other'],
            'refund_mode'                         => ['required', 'in:cash,card,bank_transfer,credit_note,upi'],
            'notes'                               => ['nullable', 'string'],

            'items'                               => ['required', 'array', 'min:1'],
            'items.*.sale_item_id'                => ['required', 'exists:sales_items,id'],
            'items.*.quantity_returned'           => ['required', 'integer', 'min:1'],
            'items.*.stock_action'                => ['required', 'in:return_to_stock,write_off'],
            'items.*.condition_note'              => ['nullable', 'string', 'max:200'],
        ];
    }
}