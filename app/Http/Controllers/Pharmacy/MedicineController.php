<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StoreMedicineRequest;
use App\Http\Requests\Pharmacy\UpdateMedicineRequest;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\MedicineUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicineController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $medicines = Medicine::query()
            ->with(['category', 'generic', 'unit'])
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->when($request->category, fn ($q) => $q->where('medicine_category_id', $request->category))
            ->when($request->form, fn ($q) => $q->where('form', $request->form))
            ->when($request->status === 'active', fn ($q) => $q->active())
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->stock === 'low', fn ($q) => $q->lowStock())
            ->when($request->stock === 'out', fn ($q) => $q->lowStock())
            ->orderBy($request->sort ?? 'name', $request->direction ?? 'asc')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($m) => [
                'id'                       => $m->id,
                'name'                     => $m->name,
                'strength'                 => $m->strength,
                'form'                     => $m->form,
                'manufacturer'             => $m->manufacturer,
                'category'                 => $m->category?->name,
                'generic'                  => $m->generic?->name,
                'unit'                     => $m->unit?->abbreviation,
                'purchase_price'           => $m->purchase_price,
                'sale_price'               => $m->sale_price,
                'total_stock'              => $m->total_stock,
                'stock_status'             => $m->stock_status,
                'nearest_expiry'           => $m->nearest_expiry,
                'reorder_level'            => $m->reorder_level,
                'is_prescription_required' => $m->is_prescription_required,
                'is_controlled'            => $m->is_controlled,
                'is_active'                => $m->is_active,
                'shelf_location'           => $m->shelf_location,
            ]);

        return Inertia::render('Pharmacy/Medicines/Index', [
            'medicines'  => $medicines,
            'categories' => MedicineCategory::active()->orderBy('name')->get(['id', 'name']),
            'filters'    => $request->only(['search', 'category', 'form', 'status', 'stock', 'sort', 'direction']),
            'summary'    => [
                'total'       => Medicine::count(),
                'active'      => Medicine::active()->count(),
                'low_stock'   => Medicine::lowStock()->count(),
                'near_expiry' => Medicine::nearExpiry(90)->count(),
            ],
        ]);
    }

    // ── Create ─────────────────────────────────────────────────────
    public function create(): Response
    {
        return Inertia::render('Pharmacy/Medicines/Create', [
            'categories' => MedicineCategory::active()->orderBy('name')->get(['id', 'name']),
            'generics'   => Generic::active()->orderBy('name')->get(['id', 'name', 'is_controlled']),
            'units'      => MedicineUnit::orderBy('name')->get(['id', 'name', 'abbreviation']),
            'forms'      => Medicine::FORMS,
        ]);
    }

    // ── Store ──────────────────────────────────────────────────────
    public function store(StoreMedicineRequest $request)
    {
        $medicine = Medicine::create($request->validated());

        return redirect()
            ->route('pharmacy.medicines.index')
            ->with('success', "Medicine \"{$medicine->name}\" created successfully.");
    }

    // ── Show ───────────────────────────────────────────────────────
    public function show(Medicine $medicine): Response
    {
        $medicine->load(['category', 'generic', 'unit', 'activeBatches.supplier']);

        return Inertia::render('Pharmacy/Medicines/Show', [
            'medicine' => [
                ...$medicine->toArray(),
                'total_stock'    => $medicine->total_stock,
                'stock_status'   => $medicine->stock_status,
                'nearest_expiry' => $medicine->nearest_expiry,
                'markup_percent' => $medicine->markup_percent,
                'batches'        => $medicine->activeBatches->map(fn ($b) => [
                    'id'                 => $b->id,
                    'batch_number'       => $b->batch_number,
                    'expiry_date'        => $b->expiry_date->toDateString(),
                    'quantity_available' => $b->quantity_available,
                    'quantity_sold'      => $b->quantity_sold,
                    'purchase_price'     => $b->purchase_price,
                    'sale_price'         => $b->sale_price,
                    'expiry_status'      => $b->expiry_status,
                    'days_to_expiry'     => $b->days_to_expiry,
                    'supplier'           => $b->supplier?->name,
                ]),
            ],
        ]);
    }

    // ── Edit ───────────────────────────────────────────────────────
    public function edit(Medicine $medicine): Response
    {
        return Inertia::render('Pharmacy/Medicines/Edit', [
            'medicine'   => $medicine->load(['category', 'generic', 'unit']),
            'categories' => MedicineCategory::active()->orderBy('name')->get(['id', 'name']),
            'generics'   => Generic::active()->orderBy('name')->get(['id', 'name', 'is_controlled']),
            'units'      => MedicineUnit::orderBy('name')->get(['id', 'name', 'abbreviation']),
            'forms'      => Medicine::FORMS,
        ]);
    }

    // ── Update ─────────────────────────────────────────────────────
    public function update(UpdateMedicineRequest $request, Medicine $medicine)
    {
        $medicine->update($request->validated());

        return redirect()
            ->route('pharmacy.medicines.index')
            ->with('success', "Medicine \"{$medicine->name}\" updated successfully.");
    }

    // ── Destroy ────────────────────────────────────────────────────
    public function destroy(Medicine $medicine)
    {
        // Safety check — cannot delete if it has stock
        if ($medicine->total_stock > 0) {
            return back()->with('error', 'Cannot delete a medicine that still has stock. Deactivate it instead.');
        }

        $medicine->delete();

        return redirect()
            ->route('pharmacy.medicines.index')
            ->with('success', "Medicine deleted.");
    }

    // ── Toggle Active ───────────────────────────────────────────────
    public function toggleActive(Medicine $medicine)
    {
        $medicine->update(['is_active' => !$medicine->is_active]);

        $status = $medicine->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Medicine {$status}.");
    }

    // ── API Search (for POS / autocomplete) ────────────────────────
    public function search(Request $request)
    {
        $medicines = Medicine::active()
            ->search($request->q)
            ->with(['generic', 'unit'])
            ->limit(15)
            ->get()
            ->map(fn ($m) => [
                'id'           => $m->id,
                'name'         => $m->name,
                'strength'     => $m->strength,
                'form'         => $m->form,
                'generic'      => $m->generic?->name,
                'unit'         => $m->unit?->abbreviation,
                'sale_price'   => $m->sale_price,
                'total_stock'  => $m->total_stock,
                'stock_status' => $m->stock_status,
            ]);

        return response()->json($medicines);
    }
}