<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StoreSalesReturnRequest;
use App\Models\Pharmacy\Sale;
use App\Models\Pharmacy\Sales;
use App\Models\Pharmacy\SalesReturn;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SalesReturnController extends Controller
{
    // ── Create (return form) ───────────────────────────────────────
    public function create(Sales $sale): Response
    {
        if (! in_array($sale->status, ['completed', 'partial_return'])) {
            return redirect()
                ->route('pharmacy.sales.show', $sale)
                ->with('error', 'This sale cannot be returned.');
        }

        $sale->load(['items.medicine.unit', 'items.stockBatch']);

        return Inertia::render('Pharmacy/SalesReturns/Create', [
            'sale' => [
                'id'             => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'sale_date'      => $sale->sale_date->toDateString(),
                'total_amount'   => $sale->total_amount,
                'payment_mode'   => $sale->payment_mode,
                'items' => $sale->items
                    ->filter(fn ($i) => $i->returnable_quantity > 0)
                    ->values()
                    ->map(fn ($i) => [
                        'id'                  => $i->id,
                        'medicine_id'         => $i->medicine_id,
                        'medicine_name'       => $i->medicine?->name,
                        'strength'            => $i->medicine?->strength,
                        'form'                => $i->medicine?->form,
                        'unit'                => $i->medicine?->unit?->abbreviation,
                        'batch_number'        => $i->stockBatch?->batch_number,
                        'expiry_date'         => $i->stockBatch?->expiry_date?->format('M Y'),
                        'stock_batch_id'      => $i->stock_batch_id,
                        'quantity'            => $i->quantity,
                        'returned_quantity'   => $i->returned_quantity,
                        'returnable_quantity' => $i->returnable_quantity,
                        'unit_price'          => $i->unit_price,
                        'discount_percent'    => $i->discount_percent,
                        'tax_percent'         => $i->tax_percent,
                    ]),
            ],
        ]);
    }

    // ── Store (process return) ─────────────────────────────────────
    public function store(StoreSalesReturnRequest $request)
    {
        $sale = Sales::findOrFail($request->sale_id);

        // Validate returnable quantities
        foreach ($request->items as $item) {
            $saleItem = $sale->items()->find($item['sale_item_id']);
            if (! $saleItem) {
                return back()->with('error', 'Invalid sale item reference.');
            }
            if ($item['quantity_returned'] > $saleItem->returnable_quantity) {
                return back()->with('error',
                    "Cannot return {$item['quantity_returned']} of {$saleItem->medicine?->name}. " .
                    "Only {$saleItem->returnable_quantity} returnable."
                );
            }
        }

        $return = DB::transaction(function () use ($request, $sale) {
            $return = SalesReturn::create([
                'sale_id'      => $sale->id,
                'returned_by'  => auth()->id(),
                'return_number'=> SalesReturn::generateReturnNumber(),
                'patient_id'   => $sale->patient_id,
                'return_date'  => $request->return_date,
                'reason'       => $request->reason,
                'refund_mode'  => $request->refund_mode,
                'status'       => 'approved',
                'notes'        => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $saleItem = $sale->items()->find($item['sale_item_id']);
                $gross    = $item['quantity_returned'] * $saleItem->unit_price;
                $disc     = $gross * ($saleItem->discount_percent / 100);
                $taxable  = $gross - $disc;
                $tax      = $taxable * ($saleItem->tax_percent / 100);

                $return->items()->create([
                    'sale_item_id'      => $item['sale_item_id'],
                    'medicine_id'       => $saleItem->medicine_id,
                    'stock_batch_id'    => $saleItem->stock_batch_id,
                    'quantity_returned' => $item['quantity_returned'],
                    'unit_price'        => $saleItem->unit_price,
                    'discount_percent'  => $saleItem->discount_percent,
                    'tax_percent'       => $saleItem->tax_percent,
                    'subtotal'          => round($taxable + $tax, 2),
                    'stock_action'      => $item['stock_action'],
                    'condition_note'    => $item['condition_note'] ?? null,
                ]);
            }

            $return->recalculateTotals();
            $return->complete();  // Updates stock + sale status

            return $return;
        });

        return redirect()
            ->route('pharmacy.sales.show', $sale)
            ->with('success', "Return {$return->return_number} processed. Refund: Rs {$return->total_return_amount}");
    }
}