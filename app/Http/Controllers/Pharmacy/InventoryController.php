<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\StockBatch;
use App\Models\Pharmacy\StockAdjustment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    // ── Main Inventory Dashboard ───────────────────────────────────
    public function index(Request $request): Response
    {
        $batches = StockBatch::query()
            ->with(['medicine.category', 'medicine.unit', 'supplier'])
            ->when($request->search, fn ($q) => $q->whereHas(
                'medicine', fn ($m) => $m->where('name', 'like', "%{$request->search}%")
            ))
            ->when($request->expiry === 'expired', fn ($q) => $q->expired())
            ->when($request->expiry === 'near', fn ($q) => $q->nearExpiry(90))
            ->when($request->expiry === 'critical', fn ($q) => $q->nearExpiry(30))
            ->when($request->category, fn ($q) => $q->whereHas(
                'medicine', fn ($m) => $m->where('medicine_category_id', $request->category)
            ))
            ->where('quantity_available', '>', 0)
            ->orderBy('expiry_date')
            ->paginate(25)
            ->withQueryString()
            ->through(fn ($b) => [
                'id'                 => $b->id,
                'medicine_id'        => $b->medicine_id,
                'medicine_name'      => $b->medicine?->name,
                'strength'           => $b->medicine?->strength,
                'form'               => $b->medicine?->form,
                'unit'               => $b->medicine?->unit?->abbreviation,
                'category'           => $b->medicine?->category?->name,
                'batch_number'       => $b->batch_number,
                'expiry_date'        => $b->expiry_date->toDateString(),
                'days_to_expiry'     => $b->days_to_expiry,
                'expiry_status'      => $b->expiry_status,
                'quantity_available' => $b->quantity_available,
                'quantity_received'  => $b->quantity_received,
                'quantity_sold'      => $b->quantity_sold,
                'purchase_price'     => $b->purchase_price,
                'sale_price'         => $b->sale_price,
                'supplier'           => $b->supplier?->name,
            ]);

        return Inertia::render('Pharmacy/Inventory/Index', [
            'batches'  => $batches,
            'filters'  => $request->only(['search', 'expiry', 'category']),
            'summary'  => $this->getInventorySummary(),
        ]);
    }

    // ── Batch Detail ───────────────────────────────────────────────
    public function batchDetail(StockBatch $batch): Response
    {
        $batch->load(['medicine.category', 'medicine.unit', 'supplier', 'goodsReceivedNote', 'saleItems.sale']);

        return Inertia::render('Pharmacy/Inventory/BatchDetail', [
            'batch' => [
                ...$batch->toArray(),
                'expiry_status'  => $batch->expiry_status,
                'days_to_expiry' => $batch->days_to_expiry,
                'medicine'       => $batch->medicine,
                'supplier'       => $batch->supplier,
                'grn'            => $batch->goodsReceivedNote,
                'transactions'   => $batch->saleItems->map(fn ($si) => [
                    'date'      => $si->sale?->sale_date,
                    'invoice'   => $si->sale?->invoice_number,
                    'quantity'  => $si->quantity,
                    'type'      => 'sale',
                ]),
            ],
        ]);
    }

    // ── Stock Adjustment ───────────────────────────────────────────
    public function adjust(Request $request)
    {
        $request->validate([
            'medicine_id'     => 'required|exists:medicines,id',
            'stock_batch_id'  => 'nullable|exists:stock_batches,id',
            'adjustment_type' => 'required|in:addition,deduction,expired,damaged,theft,correction',
            'quantity'        => 'required|integer|not_in:0',
            'reason'          => 'required|string|max:500',
        ]);

        $batch = $request->stock_batch_id
            ? StockBatch::findOrFail($request->stock_batch_id)
            : null;

        // Determine signed qty — deductions are negative
        $signedQty = in_array($request->adjustment_type, ['addition', 'correction'])
            ? abs($request->quantity)
            : -abs($request->quantity);

        // Apply to batch if specified
        if ($batch) {
            $newQty = $batch->quantity_available + $signedQty;

            if ($newQty < 0) {
                return back()->with('error', 'Adjustment would result in negative stock. Check quantity.');
            }

            $batch->update([
                'quantity_available' => $newQty,
                'quantity_adjusted'  => $batch->quantity_adjusted + abs($signedQty),
                'is_active'          => $newQty > 0,
            ]);
        }

        StockAdjustment::create([
            'medicine_id'     => $request->medicine_id,
            'stock_batch_id'  => $request->stock_batch_id,
            'adjusted_by'     => auth()->id(),
            'adjustment_type' => $request->adjustment_type,
            'quantity'        => $signedQty,
            'reason'          => $request->reason,
        ]);

        return back()->with('success', 'Stock adjustment recorded.');
    }

    // ── Private Helpers ────────────────────────────────────────────
    private function getInventorySummary(): array
    {
        return [
            'total_medicines'  => Medicine::active()->count(),
            'total_stock_value'=> StockBatch::available()
                ->selectRaw('SUM(quantity_available * purchase_price) as value')
                ->value('value') ?? 0,
            'low_stock_count'  => Medicine::lowStock()->count(),
            'near_expiry_count'=> StockBatch::nearExpiry(90)->count(),
            'expired_count'    => StockBatch::expired()->where('quantity_available', '>', 0)->count(),
            'out_of_stock'     => Medicine::active()->whereDoesntHave('activeBatches')->count(),
        ];
    }
}