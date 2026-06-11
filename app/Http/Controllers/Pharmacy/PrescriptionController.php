<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StorePrescriptionRequest;
use App\Models\Pharmacy\Prescription;
use App\Models\Pharmacy\PrescriptionItem;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\Generic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PrescriptionController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $prescriptions = Prescription::query()
            ->when($request->search, fn ($q) =>
                $q->where('prescription_number', 'like', "%{$request->search}%")
            )
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->with('dispensedBy')
            ->orderByDesc('prescribed_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($p) => [
                'id'                  => $p->id,
                'prescription_number' => $p->prescription_number,
                'prescription_date'   => $p->prescribed_at?->toDateString(),
                'patient_id'          => $p->patient_id,
                'doctor_id'           => $p->doctor_id,
                'status'              => $p->status,
                'dispensed_by'        => $p->dispensedBy?->name,
                'dispensed_at'        => $p->dispensed_at?->format('d M Y h:i A'),
            ]);

        return Inertia::render('Pharmacy/Prescriptions/Index', [
            'prescriptions' => $prescriptions,
            'filters'       => $request->only(['search', 'status']),
            'summary'       => [
                'pending'   => Prescription::where('status', 'pending')->count(),
                'partial'   => Prescription::where('status', 'partial')->count(),
                'dispensed' => Prescription::where('status', 'dispensed')->count(),
            ],
        ]);
    }

    // ── Create ─────────────────────────────────────────────────────
    public function create(): Response
    {
        return Inertia::render('Pharmacy/Prescriptions/Create', [
            'today'    => now()->toDateString(),
            'generics' => Generic::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    // ── Store ──────────────────────────────────────────────────────
    public function store(StorePrescriptionRequest $request)
    {
        $prescription = DB::transaction(function () use ($request) {
            $prescription = Prescription::create([
                'patient_id'          => $request->patient_id,
                'doctor_id'           => $request->doctor_id,
                'encounter_id'        => $request->encounter_id,
                'prescription_number' => Prescription::generatePrescriptionNumber(),
                'prescribed_at'   => $request->prescription_date ?? now(),
                'status'              => 'pending',
                'notes'               => $request->notes,
            ]);

            foreach ($request->items as $item) {
                PrescriptionItem::create([
                    'prescription_id'    => $prescription->id,
                    'medicine_id'        => $item['medicine_id']         ?? null,
                    'generic_id'         => $item['generic_id']          ?? null,
                    'dosage_instruction' => $item['dosage_instruction']  ?? null,
                    'frequency'          => $item['frequency']           ?? null,
                    'duration_days'      => $item['duration_days']       ?? null,
                    'route'              => $item['route']                ?? null,
                    'quantity_prescribed'=> $item['quantity_prescribed'],
                    'quantity_dispensed' => 0,
                    'is_substitutable'   => $item['is_substitutable']    ?? true,
                    'status'             => 'pending',
                    'instructions'       => $item['instructions']        ?? null,
                ]);
            }

            return $prescription;
        });

        return redirect()
            ->route('pharmacy.prescriptions.show', $prescription)
            ->with('success', "Prescription {$prescription->prescription_number} created.");
    }

    // ── Show ───────────────────────────────────────────────────────
    public function show(Prescription $prescription): Response
    {
        $prescription->load(['items.medicine.unit', 'items.generic', 'dispensedBy']);

        return Inertia::render('Pharmacy/Prescriptions/Show', [
            'prescription' => [
                'id'                  => $prescription->id,
                'prescription_number' => $prescription->prescription_number,
                'prescription_date'   => $prescription->prescribed_at?->toDateString(),
                'patient_id'          => $prescription->patient_id,
                'doctor_id'           => $prescription->doctor_id,
                'status'              => $prescription->status,
                'dispensed_by'        => $prescription->dispensedBy?->name,
                'dispensed_at'        => $prescription->dispensed_at?->format('d M Y h:i A'),
                'notes'               => $prescription->notes,
                'items' => $prescription->items->map(fn ($i) => [
                    'id'                  => $i->id,
                    'medicine_name'       => $i->medicine?->name ?? $i->generic?->name,
                    'strength'            => $i->medicine?->strength,
                    'form'                => $i->medicine?->form,
                    'unit'                => $i->medicine?->unit?->abbreviation,
                    'generic_name'        => $i->generic?->name,
                    'dosage_instruction'  => $i->dosage_instruction,
                    'frequency'           => $i->frequency,
                    'duration_days'       => $i->duration_days,
                    'quantity_prescribed' => $i->quantity_prescribed,
                    'quantity_dispensed'  => $i->quantity_dispensed,
                    'pending_quantity'    => $i->pending_quantity,
                    'is_substitutable'    => $i->is_substitutable,
                    'status'              => $i->status,
                ]),
            ],
        ]);
    }
}