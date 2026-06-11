<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StorePurchaseOrderRequest;
use App\Http\Requests\Pharmacy\UpdatePurchaseOrderRequest;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\PurchaseOrderItem;
use App\Models\Pharmacy\Supplier;
use App\Models\Pharmacy\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $orders = PurchaseOrder::query()
            ->with(['supplier', 'orderedBy'])
            ->when(
                $request->search,
                fn($q) =>
                $q->where('po_number', 'like', "%{$request->search}%")
                    ->orWhereHas(
                        'supplier',
                        fn($s) =>
                        $s->where('name', 'like', "%{$request->search}%")
                    )
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->supplier, fn($q) => $q->where('supplier_id', $request->supplier))
            ->orderByDesc('order_date')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($po) => [
                'id'                     => $po->id,
                'po_number'              => $po->po_number,
                'supplier'               => $po->supplier?->name,
                'supplier_id'            => $po->supplier_id,
                'order_date'             => $po->order_date->toDateString(),
                'expected_delivery_date' => $po->expected_delivery_date?->toDateString(),
                'status'                 => $po->status,
                'total_amount'           => $po->total_amount,
                'ordered_by'             => $po->orderedBy?->name,
            ]);

        return Inertia::render('Pharmacy/PurchaseOrders/Index', [
            'orders'    => $orders,
            'suppliers' => Supplier::active()->orderBy('name')->get(['id', 'name']),
            'filters'   => $request->only(['search', 'status', 'supplier']),
            'summary'   => [
                'total'    => PurchaseOrder::count(),
                'draft'    => PurchaseOrder::where('status', 'draft')->count(),
                'sent'     => PurchaseOrder::where('status', 'sent')->count(),
                'partial'  => PurchaseOrder::where('status', 'partial')->count(),
                'pending_value' => PurchaseOrder::whereIn('status', ['sent', 'partial'])
                    ->sum('total_amount'),
            ],
        ]);
    }

    // ── Create ─────────────────────────────────────────────────────
    public function create(Request $request): Response
    {
        // If coming from a supplier page, pre-select that supplier
        $selectedSupplier = $request->supplier_id
            ? Supplier::find($request->supplier_id)
            : null;

        return Inertia::render('Pharmacy/PurchaseOrders/Create', [
            'suppliers'        => Supplier::active()->orderBy('name')->get(['id', 'name', 'credit_days']),
            'selected_supplier' => $selectedSupplier ? ['id' => $selectedSupplier->id, 'name' => $selectedSupplier->name] : null,
            'today'            => now()->toDateString(),
        ]);
    }

    // ── Store ──────────────────────────────────────────────────────
    public function store(StorePurchaseOrderRequest $request)
    {
        $po = DB::transaction(function () use ($request) {
            $po = PurchaseOrder::create([
                'supplier_id'            => $request->supplier_id,
                'ordered_by'             => auth()->id(),
                'po_number'              => PurchaseOrder::generatePoNumber(),
                'order_date'             => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status'                 => 'draft',
                'notes'                  => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $this->calculateSubtotal(
                    $item['quantity_ordered'],
                    $item['unit_price'],
                    $item['discount_percent'],
                    $item['tax_percent']
                );

                $po->items()->create([
                    'medicine_id'      => $item['medicine_id'],
                    'quantity_ordered'  => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_price'        => $item['unit_price'],
                    'discount_percent'  => $item['discount_percent'],
                    'tax_percent'       => $item['tax_percent'],
                    'subtotal'          => $subtotal,
                ]);
            }

            $po->recalculateTotals();
            return $po;
        });

        return redirect()
            ->route('pharmacy.purchase-orders.show', $po)
            ->with('success', "Purchase Order {$po->po_number} created.");
    }

    // ── Show ───────────────────────────────────────────────────────
    public function show(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load([
            'supplier',
            'orderedBy',
            'approvedBy',
            'items.medicine.unit',
            'goodsReceivedNotes',
        ]);

        return Inertia::render('Pharmacy/PurchaseOrders/Show', [
            'order' => [
                'id'                     => $purchaseOrder->id,
                'po_number'              => $purchaseOrder->po_number,
                'order_date'             => $purchaseOrder->order_date->toDateString(),
                'expected_delivery_date' => $purchaseOrder->expected_delivery_date?->toDateString(),
                'status'                 => $purchaseOrder->status,
                'subtotal'               => $purchaseOrder->subtotal,
                'discount_amount'        => $purchaseOrder->discount_amount,
                'tax_amount'             => $purchaseOrder->tax_amount,
                'total_amount'           => $purchaseOrder->total_amount,
                'notes'                  => $purchaseOrder->notes,
                'supplier'               => $purchaseOrder->supplier,
                'ordered_by'             => $purchaseOrder->orderedBy?->name,
                'approved_by'            => $purchaseOrder->approvedBy?->name,
                'items'                  => $purchaseOrder->items->map(fn($item) => [
                    'id'               => $item->id,
                    'medicine_id'      => $item->medicine_id,
                    'medicine_name'    => $item->medicine?->name,
                    'strength'         => $item->medicine?->strength,
                    'form'             => $item->medicine?->form,
                    'unit'             => $item->medicine?->unit?->abbreviation,
                    'quantity_ordered'  => $item->quantity_ordered,
                    'quantity_received' => $item->quantity_received,
                    'pending_quantity'  => $item->pending_quantity,
                    'unit_price'        => $item->unit_price,
                    'discount_percent'  => $item->discount_percent,
                    'tax_percent'       => $item->tax_percent,
                    'subtotal'          => $item->subtotal,
                ]),
                'grns' => $purchaseOrder->goodsReceivedNotes->map(fn($grn) => [
                    'id'          => $grn->id,
                    'grn_number'  => $grn->grn_number,
                    'received_date' => $grn->received_date->toDateString(),
                    'status'      => $grn->status,
                    'total_amount' => $grn->total_amount,
                ]),
            ],
        ]);
    }

    // ── Edit ───────────────────────────────────────────────────────
    public function edit(PurchaseOrder $purchaseOrder): Response
    {
        // here is bug I think
        // if (! in_array($purchaseOrder->status, ['draft'])) {
        //     return redirect()->route('pharmacy.purchase-orders.show', $purchaseOrder)->with('error', 'Only draft purchase orders can be edited.');
        // }

        $purchaseOrder->load(['supplier', 'items.medicine.unit']);

        return Inertia::render('Pharmacy/PurchaseOrders/Create', [
            'suppliers'         => Supplier::active()->orderBy('name')->get(['id', 'name', 'credit_days']),
            'selected_supplier' => ['id' => $purchaseOrder->supplier_id, 'name' => $purchaseOrder->supplier?->name],
            'today'             => now()->toDateString(),
            'editing'           => [
                'id'                     => $purchaseOrder->id,
                'po_number'              => $purchaseOrder->po_number,
                'supplier_id'            => $purchaseOrder->supplier_id,
                'order_date'             => $purchaseOrder->order_date->toDateString(),
                'expected_delivery_date' => $purchaseOrder->expected_delivery_date?->toDateString(),
                'notes'                  => $purchaseOrder->notes,
                'items'                  => $purchaseOrder->items->map(fn($i) => [
                    'id'              => $i->id,
                    'medicine_id'     => $i->medicine_id,
                    'medicine_name'   => $i->medicine?->name,
                    'strength'        => $i->medicine?->strength,
                    'form'            => $i->medicine?->form,
                    'unit'            => $i->medicine?->unit?->abbreviation,
                    'quantity_ordered' => $i->quantity_ordered,
                    'unit_price'      => $i->unit_price,
                    'discount_percent' => $i->discount_percent,
                    'tax_percent'     => $i->tax_percent,
                    'subtotal'        => $i->subtotal,
                ]),
            ],
        ]);
    }

    // ── Update ─────────────────────────────────────────────────────
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Only draft purchase orders can be edited.');
        }

        DB::transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->update([
                'supplier_id'            => $request->supplier_id,
                'order_date'             => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes'                  => $request->notes,
            ]);

            // Sync items
            $existingIds = collect($request->items)->pluck('id')->filter()->all();
            $purchaseOrder->items()->whereNotIn('id', $existingIds)->delete();

            foreach ($request->items as $itemData) {
                $subtotal = $this->calculateSubtotal(
                    $itemData['quantity_ordered'],
                    $itemData['unit_price'],
                    $itemData['discount_percent'],
                    $itemData['tax_percent']
                );

                $payload = [
                    'medicine_id'      => $itemData['medicine_id'],
                    'quantity_ordered'  => $itemData['quantity_ordered'],
                    'unit_price'        => $itemData['unit_price'],
                    'discount_percent'  => $itemData['discount_percent'],
                    'tax_percent'       => $itemData['tax_percent'],
                    'subtotal'          => $subtotal,
                ];

                if (! empty($itemData['id'])) {
                    $purchaseOrder->items()->where('id', $itemData['id'])->update($payload);
                } else {
                    $purchaseOrder->items()->create(array_merge($payload, ['quantity_received' => 0]));
                }
            }

            $purchaseOrder->recalculateTotals();
        });

        return redirect()
            ->route('pharmacy.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated.');
    }

    // ── Send to Supplier ───────────────────────────────────────────
    public function send(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Only draft orders can be marked as sent.');
        }

        $purchaseOrder->update(['status' => 'sent']);

        return back()->with('success', "PO {$purchaseOrder->po_number} marked as sent to supplier.");
    }

    // ── Cancel ─────────────────────────────────────────────────────
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, ['draft', 'sent'])) {
            return back()->with('error', 'Only draft or sent orders can be cancelled.');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return back()->with('success', "PO {$purchaseOrder->po_number} cancelled.");
    }

    // ── Destroy ────────────────────────────────────────────────────
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Only draft purchase orders can be deleted.');
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()
            ->route('pharmacy.purchase-orders.index')
            ->with('success', 'Purchase order deleted.');
    }

    // ── Private: subtotal calculation ─────────────────────────────
    private function calculateSubtotal(int $qty, float $price, float $discount, float $tax): float
    {
        $gross   = $qty * $price;
        $after   = $gross - ($gross * $discount / 100);
        return round($after + ($after * $tax / 100), 2);
    }
}
