<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StoreGrnRequest;
use App\Models\Pharmacy\GoodsReceivedNote;
use App\Models\Pharmacy\GrnItem;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GrnController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $grns = GoodsReceivedNote::query()
            ->with(['supplier', 'receivedBy'])
            ->when($request->search, fn ($q) =>
                $q->where('grn_number', 'like', "%{$request->search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$request->search}%"))
            )
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->supplier, fn ($q) => $q->where('supplier_id', $request->supplier))
            ->orderByDesc('received_date')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($grn) => [
                'id'             => $grn->id,
                'grn_number'     => $grn->grn_number,
                'received_date'  => $grn->received_date->toDateString(),
                'supplier'       => $grn->supplier?->name,
                'supplier_id'    => $grn->supplier_id,
                'status'         => $grn->status,
                'total_amount'   => $grn->total_amount,
                'received_by'    => $grn->receivedBy?->name,
                'items_count'    => $grn->items()->count(),
            ]);

        return Inertia::render('Pharmacy/GRN/Index', [
            'grns'      => $grns,
            'suppliers' => Supplier::active()->orderBy('name')->get(['id', 'name']),
            'filters'   => $request->only(['search', 'status', 'supplier']),
            'summary'   => [
                'total'    => GoodsReceivedNote::count(),
                'pending'  => GoodsReceivedNote::where('status', 'pending')->count(),
                'verified' => GoodsReceivedNote::where('status', 'verified')->count(),
                'posted'   => GoodsReceivedNote::where('status', 'posted')->count(),
            ],
        ]);
    }

    // ── Create ─────────────────────────────────────────────────────
    public function create(Request $request): Response
    {
        // Pre-load PO data if coming from a purchase order
        $purchaseOrder = null;
        if ($request->purchase_order_id) {
            $purchaseOrder = PurchaseOrder::with(['supplier', 'items.medicine.unit'])
                ->findOrFail($request->purchase_order_id);
// we have to fix at here later
            // if (! in_array($purchaseOrder->status, ['sent', 'partial'])) {
            //     return redirect()
            //         ->route('pharmacy.purchase-orders.show', $purchaseOrder)
            //         ->with('error', 'GRN can only be created for sent or partially received orders.');
            // }
        }

        return Inertia::render('Pharmacy/GRN/Create', [
            'suppliers'      => Supplier::active()->orderBy('name')->get(['id', 'name']),
            'today'          => now()->toDateString(),
            'purchase_order' => $purchaseOrder ? [
                'id'        => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'supplier'  => ['id' => $purchaseOrder->supplier_id, 'name' => $purchaseOrder->supplier?->name],
                'items'     => $purchaseOrder->items
                    ->filter(fn ($i) => $i->pending_quantity > 0)
                    ->values()
                    ->map(fn ($i) => [
                        'id'               => $i->id,
                        'medicine_id'      => $i->medicine_id,
                        'medicine_name'    => $i->medicine?->name,
                        'strength'         => $i->medicine?->strength,
                        'form'             => $i->medicine?->form,
                        'unit'             => $i->medicine?->unit?->abbreviation,
                        'quantity_ordered'  => $i->quantity_ordered,
                        'quantity_received' => $i->quantity_received,
                        'pending_quantity'  => $i->pending_quantity,
                        'unit_price'        => $i->unit_price,
                    ]),
            ] : null,
        ]);
    }

    // ── Store ──────────────────────────────────────────────────────
    public function store(StoreGrnRequest $request)
    {
        $grn = DB::transaction(function () use ($request) {
            $grn = GoodsReceivedNote::create([
                'supplier_id'       => $request->supplier_id,
                'purchase_order_id' => $request->purchase_order_id,
                'received_by'       => auth()->id(),
                'grn_number'        => GoodsReceivedNote::generateGrnNumber(),
                'received_date'     => $request->received_date,
                'invoice_number'    => $request->invoice_number,
                'invoice_date'      => $request->invoice_date,
                'status'            => 'pending',
                'notes'             => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $this->calculateSubtotal(
                    $item['quantity_received'],
                    $item['unit_price'],
                    $item['discount_percent'],
                    $item['tax_percent']
                );

                GrnItem::create([
                    'goods_received_note_id'  => $grn->id,
                    'medicine_id'             => $item['medicine_id'],
                    'purchase_order_item_id'  => $item['purchase_order_item_id'] ?? null,
                    'batch_number'            => $item['batch_number'],
                    'manufacturing_date'      => $item['manufacturing_date'] ?? null,
                    'expiry_date'             => $item['expiry_date'],
                    'quantity_received'       => $item['quantity_received'],
                    'free_quantity'           => $item['free_quantity'] ?? 0,
                    'unit_price'              => $item['unit_price'],
                    'sale_price'              => $item['sale_price'],
                    'mrp'                     => $item['mrp'] ?? null,
                    'discount_percent'        => $item['discount_percent'],
                    'tax_percent'             => $item['tax_percent'],
                    'subtotal'                => $subtotal,
                ]);
            }

            $grn->recalculateTotals();
            return $grn;
        });

        return redirect()
            ->route('pharmacy.grn.show', $grn)
            ->with('success', "GRN {$grn->grn_number} created. Please verify and post to update stock.");
    }

    // ── Show ───────────────────────────────────────────────────────
    public function show(GoodsReceivedNote $grn): Response
    {
        $grn->load([
            'supplier',
            'purchaseOrder',
            'receivedBy',
            'verifiedBy',
            'items.medicine.unit',
            'items.stockBatch',
        ]);

        return Inertia::render('Pharmacy/GRN/Show', [
            'grn' => [
                'id'              => $grn->id,
                'grn_number'      => $grn->grn_number,
                'received_date'   => $grn->received_date->toDateString(),
                'invoice_number'  => $grn->invoice_number,
                'invoice_date'    => $grn->invoice_date?->toDateString(),
                'status'          => $grn->status,
                'subtotal'        => $grn->subtotal,
                'discount_amount' => $grn->discount_amount,
                'tax_amount'      => $grn->tax_amount,
                'total_amount'    => $grn->total_amount,
                'notes'           => $grn->notes,
                'supplier'        => $grn->supplier,
                'purchase_order'  => $grn->purchaseOrder
                    ? ['id' => $grn->purchaseOrder->id, 'po_number' => $grn->purchaseOrder->po_number]
                    : null,
                'received_by'     => $grn->receivedBy?->name,
                'verified_by'     => $grn->verifiedBy?->name,
                'items'           => $grn->items->map(fn ($item) => [
                    'id'               => $item->id,
                    'medicine_id'      => $item->medicine_id,
                    'medicine_name'    => $item->medicine?->name,
                    'strength'         => $item->medicine?->strength,
                    'form'             => $item->medicine?->form,
                    'unit'             => $item->medicine?->unit?->abbreviation,
                    'batch_number'     => $item->batch_number,
                    'manufacturing_date'=> $item->manufacturing_date?->toDateString(),
                    'expiry_date'      => $item->expiry_date->toDateString(),
                    'quantity_received' => $item->quantity_received,
                    'free_quantity'    => $item->free_quantity,
                    'total_quantity'   => $item->total_quantity,
                    'unit_price'       => $item->unit_price,
                    'sale_price'       => $item->sale_price,
                    'mrp'              => $item->mrp,
                    'discount_percent'  => $item->discount_percent,
                    'tax_percent'      => $item->tax_percent,
                    'subtotal'         => $item->subtotal,
                    'stock_batch_id'   => $item->stock_batch_id,
                    'batch_number_posted' => $item->stockBatch?->batch_number,
                ]),
            ],
        ]);
    }

    // ── Verify ─────────────────────────────────────────────────────
    public function verify(GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'pending') {
            return back()->with('error', 'Only pending GRNs can be verified.');
        }

        $grn->update([
            'status'      => 'verified',
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', "GRN {$grn->grn_number} verified. Ready to post.");
    }

    // ── Post ── THE KEY ACTION: creates stock batches ──────────────
    public function post(GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'verified') {
            return back()->with('error', 'GRN must be verified before posting.');
        }

        try {
            $grn->post();  // Defined in GoodsReceivedNote model
        } catch (\Exception $e) {
            return back()->with('error', "Failed to post GRN: {$e->getMessage()}");
        }

        return redirect()
            ->route('pharmacy.grn.show', $grn)
            ->with('success', "GRN {$grn->grn_number} posted successfully. Stock batches created and inventory updated.");
    }

    // ── Destroy (only pending GRNs) ────────────────────────────────
    public function destroy(GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'pending') {
            return back()->with('error', 'Only pending GRNs can be deleted.');
        }

        DB::transaction(function () use ($grn) {
            $grn->items()->delete();
            $grn->delete();
        });

        return redirect()
            ->route('pharmacy.purchase-orders.index')
            ->with('success', 'GRN deleted.');
    }

    // ── Private: subtotal calculation ─────────────────────────────
    private function calculateSubtotal(int $qty, float $price, float $discount, float $tax): float
    {
        $gross  = $qty * $price;
        $after  = $gross - ($gross * $discount / 100);
        return round($after + ($after * $tax / 100), 2);
    }
}