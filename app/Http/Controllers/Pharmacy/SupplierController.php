<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StoreSupplierRequest;
use App\Http\Requests\Pharmacy\UpdateSupplierRequest;
use App\Models\Pharmacy\Supplier;
use App\Models\Pharmacy\PurchaseOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $suppliers = Supplier::query()
            ->when($request->search, fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('drug_license_no', 'like', "%{$request->search}%")
            )
            ->when($request->status === 'active',   fn ($q) => $q->active())
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->withCount('purchaseOrders')
            ->withSum(['purchaseOrders as total_purchases' => fn ($q) =>
                $q->whereIn('status', ['received', 'partial'])
            ], 'total_amount')
            ->orderBy($request->sort ?? 'name', $request->direction ?? 'asc')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($s) => [
                'id'                   => $s->id,
                'name'                 => $s->name,
                'contact_person'       => $s->contact_person,
                'phone'                => $s->phone,
                'email'                => $s->email,
                'city'                 => $s->city,
                'drug_license_no'      => $s->drug_license_no,
                'drug_license_expiry'  => $s->drug_license_expiry?->toDateString(),
                'is_license_expired'   => $s->is_license_expired,
                'credit_days'          => $s->credit_days,
                'credit_limit'         => $s->credit_limit,
                'purchase_orders_count'=> $s->purchase_orders_count,
                'total_purchases'      => $s->total_purchases ?? 0,
                'is_active'            => $s->is_active,
            ]);

        return Inertia::render('Pharmacy/Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters'   => $request->only(['search', 'status', 'sort', 'direction']),
            'summary'   => [
                'total'    => Supplier::count(),
                'active'   => Supplier::active()->count(),
                'expiring' => Supplier::active()
                    ->whereNotNull('drug_license_expiry')
                    ->whereDate('drug_license_expiry', '<=', now()->addDays(30))
                    ->count(),
            ],
        ]);
    }

    // ── Create ─────────────────────────────────────────────────────
    public function create(): Response
    {
        return Inertia::render('Pharmacy/Suppliers/Create');
    }

    // ── Store ──────────────────────────────────────────────────────
    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return redirect()
            ->route('pharmacy.suppliers.show', $supplier)
            ->with('success', "Supplier \"{$supplier->name}\" created successfully.");
    }

    // ── Show ───────────────────────────────────────────────────────
    public function show(Supplier $supplier): Response
    {
        $supplier->loadCount('purchaseOrders');

        $recentOrders = $supplier->purchaseOrders()
            ->with('orderedBy')
            ->latest('order_date')
            ->limit(10)
            ->get()
            ->map(fn ($po) => [
                'id'           => $po->id,
                'po_number'    => $po->po_number,
                'order_date'   => $po->order_date->toDateString(),
                'status'       => $po->status,
                'total_amount' => $po->total_amount,
                'ordered_by'   => $po->orderedBy?->name,
            ]);

        return Inertia::render('Pharmacy/Suppliers/Show', [
            'supplier' => [
                ...$supplier->toArray(),
                'is_license_expired'    => $supplier->is_license_expired,
                'total_purchases'       => $supplier->total_purchases,
                'purchase_orders_count' => $supplier->purchase_orders_count,
                'recent_orders'         => $recentOrders,
            ],
        ]);
    }

    // ── Edit ───────────────────────────────────────────────────────
    public function edit(Supplier $supplier): Response
    {
        return Inertia::render('Pharmacy/Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    // ── Update ─────────────────────────────────────────────────────
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return redirect()
            ->route('pharmacy.suppliers.show', $supplier)
            ->with('success', "Supplier updated successfully.");
    }

    // ── Destroy ────────────────────────────────────────────────────
    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Cannot delete supplier with existing purchase orders. Deactivate instead.');
        }

        $supplier->delete();

        return redirect()
            ->route('pharmacy.suppliers.index')
            ->with('success', 'Supplier deleted.');
    }

    // ── Toggle Active ──────────────────────────────────────────────
    public function toggleActive(Supplier $supplier)
    {
        $supplier->update(['is_active' => ! $supplier->is_active]);

        return back()->with('success', 'Supplier ' . ($supplier->is_active ? 'activated' : 'deactivated') . '.');
    }

    // ── Search (for Combobox / autocomplete) ──────────────────────
    public function search(Request $request)
    {
        return response()->json(
            Supplier::active()
                ->where('name', 'like', "%{$request->q}%")
                ->limit(15)
                ->get(['id', 'name', 'phone', 'credit_days'])
        );
    }
}   