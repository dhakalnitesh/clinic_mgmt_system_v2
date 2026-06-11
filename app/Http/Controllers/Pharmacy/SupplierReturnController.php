<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\StockBatch;
use App\Models\Pharmacy\Supplier;
use App\Models\Pharmacy\SupplierReturn;
use App\Models\Pharmacy\SupplierReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SupplierReturnController extends Controller
{
    public function index(Request $request): Response
    {
        $returns = SupplierReturn::query()
            ->with(['supplier', 'returnedBy'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->supplier, fn ($q) => $q->where('supplier_id', $request->supplier))
            ->orderByDesc('return_date')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($r) => [
                'id'             => $r->id,
                'return_number'  => $r->return_number,
                'return_date'    => $r->return_date->toDateString(),
                'supplier'       => $r->supplier?->name,
                'status'         => $r->status,
                'total_amount'   => $r->total_amount,
                'returned_by'    => $r->returnedBy?->name,
            ]);

        return Inertia::render('Pharmacy/SupplierReturns/Index', [
            'returns'   => $returns,
            'suppliers' => Supplier::active()->orderBy('name')->get(['id', 'name']),
            'filters'   => $request->only(['status', 'supplier']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pharmacy/SupplierReturns/Create', [
            'suppliers' => Supplier::active()->orderBy('name')->get(['id', 'name']),
            'today'     => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'            => ['required', 'exists:suppliers,id'],
            'return_date'            => ['required', 'date'],
            'reason'                 => ['nullable', 'string'],
            'notes'                  => ['nullable', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.stock_batch_id' => ['required', 'exists:stock_batches,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.reason'         => ['nullable', 'string'],
        ]);

        $supplierReturn = DB::transaction(function () use ($validated) {
            $return = SupplierReturn::create([
                'supplier_id'   => $validated['supplier_id'],
                'returned_by'   => auth()->id(),
                'return_number' => SupplierReturn::generateReturnNumber(),
                'return_date'   => $validated['return_date'],
                'reason'        => $validated['reason'] ?? 'damaged',
                'status'        => 'draft',
                'notes'         => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $batch = StockBatch::findOrFail($item['stock_batch_id']);
                SupplierReturnItem::create([
                    'supplier_return_id' => $return->id,
                    'medicine_id'        => $batch->medicine_id,
                    'stock_batch_id'     => $item['stock_batch_id'],
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $batch->purchase_price,
                    'subtotal'           => $item['quantity'] * $batch->purchase_price,
                    'reason'             => $item['reason'] ?? $validated['reason'] ?? 'damaged',
                ]);
            }

            $return->recalculateTotals();
            return $return;
        });

        return redirect()
            ->route('pharmacy.supplier-returns.show', $supplierReturn)
            ->with('success', "Supplier return {$supplierReturn->return_number} created.");
    }

    public function show(SupplierReturn $supplierReturn): Response
    {
        $supplierReturn->load(['supplier', 'returnedBy', 'items.stockBatch.medicine.unit']);

        return Inertia::render('Pharmacy/SupplierReturns/Show', [
            'return' => [
                'id'            => $supplierReturn->id,
                'return_number' => $supplierReturn->return_number,
                'return_date'   => $supplierReturn->return_date->toDateString(),
                'supplier'      => $supplierReturn->supplier?->name,
                'status'        => $supplierReturn->status,
                'reason'        => $supplierReturn->reason,
                'total_amount'  => $supplierReturn->total_amount,
                'returned_by'   => $supplierReturn->returnedBy?->name,
                'notes'         => $supplierReturn->notes,
                'items'         => $supplierReturn->items->map(fn ($i) => [
                    'id'               => $i->id,
                    'medicine_name'    => $i->stockBatch?->medicine?->name,
                    'batch_number'     => $i->stockBatch?->batch_number,
                    'quantity'         => $i->quantity,
                    'unit_price'       => $i->unit_price,
                    'subtotal'         => $i->subtotal,
                    'reason'           => $i->reason,
                ]),
            ],
        ]);
    }

    public function complete(SupplierReturn $supplierReturn)
    {
        if ($supplierReturn->status !== 'draft') {
            return back()->with('error', 'Only draft returns can be completed.');
        }

        $supplierReturn->complete();

        return redirect()
            ->route('pharmacy.supplier-returns.show', $supplierReturn)
            ->with('success', "Return {$supplierReturn->return_number} completed. Stock deducted.");
    }
}
