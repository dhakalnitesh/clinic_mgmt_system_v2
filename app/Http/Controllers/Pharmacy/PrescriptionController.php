<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StorePrescriptionRequest;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\Prescription;
use App\Models\Pharmacy\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PrescriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Prescription::query()
            ->with(['patient', 'doctor', 'dispensedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('doctor', fn ($d) => $d->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('prescribed_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('prescribed_at', '<=', $request->to_date);
        }

        $perPage = $request->integer('per_page', 20);

        $prescriptions = $query
            ->orderByDesc('prescribed_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($p) => [
                'id'                  => $p->id,
                'prescription_number' => $p->prescription_number,
                'prescribed_at'       => $p->prescribed_at?->toDateString(),
                'created_at_bs'       => $p->created_at_bs,
                'patient_id'          => $p->patient_id,
                'patient_name'        => $p->patient?->name,
                'doctor_id'           => $p->doctor_id,
                'doctor_name'         => $p->doctor?->name,
                'status'              => $p->status,
                'dispensed_by'        => $p->dispensedBy?->name,
                'dispensed_at'        => $p->dispensed_at?->format('d M Y h:i A'),
                'items_count'         => $p->items()->count(),
            ]);

        return Inertia::render('Pharmacy/Prescriptions/Index', [
            'prescriptions' => $prescriptions,
            'filters'       => $request->only(['search', 'status', 'from_date', 'to_date', 'per_page']),
            'summary'       => [
                'total'     => Prescription::count(),
                'pending'   => Prescription::where('status', 'pending')->count(),
                'partial'   => Prescription::where('status', 'partial')->count(),
                'dispensed' => Prescription::where('status', 'dispensed')->count(),
            ],
            'patients'      => Patient::select('id', 'name', 'phone', 'uhid')->orderBy('name')->get(),
            'doctors'       => Doctor::select('id', 'name', 'specialization')->orderBy('name')->get(),
            'generics'      => Generic::active()->orderBy('name')->get(['id', 'name']),
            'today_bs'      => \Anuzpandey\LaravelNepaliDate\LaravelNepaliDate::from(now()->format('Y-m-d'))->toNepaliDate(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pharmacy/Prescriptions/Create', [
            'today'    => now()->toDateString(),
            'generics' => Generic::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StorePrescriptionRequest $request)
    {
        $prescription = DB::transaction(function () use ($request) {
            $prescription = Prescription::create([
                'patient_id'          => $request->patient_id,
                'doctor_id'           => $request->doctor_id,
                'encounter_id'        => $request->encounter_id,
                'prescription_number' => Prescription::generatePrescriptionNumber(),
                'prescribed_at'       => $request->prescription_date ?? now(),
                'status'              => 'pending',
                'notes'               => $request->notes,
                'created_by'          => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                PrescriptionItem::create([
                    'prescription_id'     => $prescription->id,
                    'medicine_id'         => $item['medicine_id']         ?? null,
                    'medicine_name'       => $item['medicine_name']       ?? null,
                    'generic_id'          => $item['generic_id']          ?? null,
                    'dosage_instruction'  => $item['dosage_instruction']  ?? null,
                    'frequency'           => $item['frequency']           ?? null,
                    'duration_days'       => $item['duration_days']       ?? null,
                    'route'               => $item['route']               ?? null,
                    'quantity_prescribed' => $item['quantity_prescribed'],
                    'quantity_dispensed'  => 0,
                    'is_substitutable'    => $item['is_substitutable']    ?? true,
                    'status'              => 'pending',
                    'instructions'        => $item['instructions']        ?? null,
                ]);
            }

            return $prescription;
        });

        return redirect()
            ->route('prescriptions.index')
            ->with('success', "Prescription {$prescription->prescription_number} created.");
    }

    public function show(Prescription $prescription): Response
    {
        $prescription->load(['patient', 'doctor', 'items.medicine.unit', 'items.generic', 'dispensedBy']);

        return Inertia::render('Pharmacy/Prescriptions/Show', [
            'prescription' => [
                'id'                  => $prescription->id,
                'prescription_number' => $prescription->prescription_number,
                'prescribed_at'       => $prescription->prescribed_at?->toDateString(),
                'created_at_bs'       => $prescription->created_at_bs,
                'patient_id'          => $prescription->patient_id,
                'patient_name'        => $prescription->patient?->name,
                'doctor_id'           => $prescription->doctor_id,
                'doctor_name'         => $prescription->doctor?->name,
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

    public function print(Prescription $prescription): Response
    {
        $prescription->load(['patient', 'doctor', 'items.medicine.unit', 'items.generic', 'dispensedBy']);

        return Inertia::render('Pharmacy/Prescriptions/Print', [
            'prescription' => [
                'id'                  => $prescription->id,
                'prescription_number' => $prescription->prescription_number,
                'prescribed_at'       => $prescription->prescribed_at?->toDateString(),
                'created_at_bs'       => $prescription->created_at_bs,
                'patient_id'          => $prescription->patient_id,
                'patient_name'        => $prescription->patient?->name,
                'doctor_id'           => $prescription->doctor_id,
                'doctor_name'         => $prescription->doctor?->name,
                'status'              => $prescription->status,
                'notes'               => $prescription->notes,
                'items' => $prescription->items->map(fn ($i) => [
                    'id'                  => $i->id,
                    'medicine_name'       => $i->medicine?->name ?? $i->generic?->name,
                    'strength'            => $i->medicine?->strength,
                    'form'                => $i->medicine?->form,
                    'unit'                => $i->medicine?->unit?->abbreviation,
                    'dosage_instruction'  => $i->dosage_instruction,
                    'frequency'           => $i->frequency,
                    'duration_days'       => $i->duration_days,
                    'quantity_prescribed' => $i->quantity_prescribed,
                    'is_substitutable'    => $i->is_substitutable,
                    'instructions'        => $i->instructions,
                ]),
            ],
        ]);
    }
}
