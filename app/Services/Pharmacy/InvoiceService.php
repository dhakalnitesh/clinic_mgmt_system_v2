<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacy\Sales;

class InvoiceService
{
    /**
     * Build structured print data for an invoice.
     */
    public static function buildPrintData(Sales $sale): array
    {
        $sale->loadMissing(['items.medicine.unit', 'items.stockBatch', 'cashier', 'pharmacist', 'prescription']);

        $items = $sale->items->map(fn ($item) => [
            'name'       => ($item->medicine?->name ?? 'Unknown') . ($item->medicine?->strength ? ' ' . $item->medicine->strength : ''),
            'form'       => $item->medicine?->form,
            'batch'      => $item->stockBatch?->batch_number ?? '-',
            'expiry'     => $item->stockBatch?->expiry_date?->format('M Y') ?? '-',
            'qty'        => $item->quantity,
            'unit_price' => number_format($item->unit_price, 2),
            'discount'   => $item->discount_percent . '%',
            'subtotal'   => number_format($item->subtotal, 2),
        ]);

        $discountLabel = $sale->discount_type === 'percent'
            ? $sale->discount_value . '%'
            : number_format($sale->discount_value, 2);

        return [
            'title'             => 'Pharmacy Invoice',
            'invoice_number'    => $sale->invoice_number,
            'sale_date'         => $sale->sale_date->format('d M Y'),
            'sale_type'         => ucfirst($sale->sale_type),
            'cashier'           => $sale->cashier?->name ?? '-',
            'pharmacist'        => $sale->pharmacist?->name ?? '-',
            'prescription'      => $sale->prescription?->prescription_number ?? '-',
            'items'             => $items,
            'subtotal'          => number_format($sale->subtotal, 2),
            'discount_label'    => $discountLabel,
            'discount_amount'   => number_format($sale->discount_amount, 2),
            'tax_amount'        => number_format($sale->tax_amount, 2),
            'total_amount'      => number_format($sale->total_amount, 2),
            'paid_amount'       => number_format($sale->paid_amount, 2),
            'change_amount'     => number_format($sale->change_amount, 2),
            'payment_mode'      => ucfirst(str_replace('_', ' ', $sale->payment_mode)),
            'payment_reference' => $sale->payment_reference ?? '-',
            'notes'             => $sale->notes ?? '',
            'status'            => $sale->status,
        ];
    }
}
