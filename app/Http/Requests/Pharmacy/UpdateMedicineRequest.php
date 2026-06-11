<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('medicine'));
    }

    public function rules(): array
    {
        $id = $this->route('medicine')->id;

        return [
            'medicine_category_id'     => ['required', 'exists:medicine_categories,id'],
            'generic_id'               => ['required', 'exists:generics,id'],
            'medicine_unit_id'         => ['required', 'exists:medicine_units,id'],
            'name'                     => ['required', 'string', 'max:200'],
            'strength'                 => ['nullable', 'string', 'max:80'],
            'form'                     => ['required', Rule::in([
                'tablet','capsule','syrup','suspension','injection',
                'cream','ointment','gel','drops','inhaler','patch',
                'suppository','powder','lotion','solution','other',
            ])],
            'manufacturer'             => ['nullable', 'string', 'max:150'],
            'barcode'                  => ['nullable', 'string', 'max:100', Rule::unique('medicines', 'barcode')->ignore($id)],
            'hsn_code'                 => ['nullable', 'string', 'max:20'],
            'purchase_price'           => ['required', 'numeric', 'min:0'],
            'sale_price'               => ['required', 'numeric', 'min:0'],
            'mrp'                      => ['nullable', 'numeric', 'min:0'],
            'tax_percent'              => ['required', 'numeric', 'min:0', 'max:100'],
            'reorder_level'            => ['required', 'integer', 'min:0'],
            'reorder_quantity'         => ['required', 'integer', 'min:1'],
            'shelf_location'           => ['nullable', 'string', 'max:50'],
            'is_prescription_required' => ['boolean'],
            'is_controlled'            => ['boolean'],
            'is_active'                => ['boolean'],
            'notes'                    => ['nullable', 'string'],
        ];
    }
}