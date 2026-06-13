<?php
namespace App\Http\Controllers\Consultation;
use App\Http\Controllers\Controller;

use App\Models\Consultation\Consultation;
use App\Models\FollowUp\FollowUp;
use App\Models\Laboratory\LabOrder;
use App\Models\Laboratory\LabOrderItem;
use App\Models\Laboratory\LabTest;
use App\Models\Laboratory\LabTestParameter;
use App\Models\Pharmacy\Prescription;
use App\Models\Pharmacy\PrescriptionItem;
use App\Models\Visit\Visit;
use App\Models\Vitals\Vital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $query = Consultation::query()
            ->with([
                'patient:id,name,phone,uhid',
                'doctor:id,name',
            ])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('uhid', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('consultation_status', $request->status);
        }

        $consultations = $query->paginate(10)->withQueryString();

        return Inertia::render('Consultations/Index', [
            'consultations' => $consultations,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(Consultation $consultation)
    {
        $consultation->load([
            'patient',
            'doctor',
            'vitals',
            'prescriptions.items',
        ]);

        return Inertia::render('Consultations/Show', [
            'consultation' => $consultation,
        ]);
    }

    public function create(Visit $visit)
    {
        $visit->load(['patient', 'doctor']);

        $vitals = Vital::where('visit_id', $visit->id)
            ->latest()
            ->first();

        $recentMedicines = PrescriptionItem::query()
            ->whereHas('prescription.consultation', function ($query) use ($visit) {
                $query->where('patient_id', $visit->patient_id);
            })
            ->latest()
            ->limit(5)
            ->get();

        $visit->update(['status' => 'in_consultation']);

        return Inertia::render('Consultations/Create', [
            'selectedPatient' => $visit->patient,
            'visit' => [
                'id' => $visit->id,
                'appointment_id' => $visit->appointment_id,
                'type' => $visit->type,
                'status' => $visit->status,
                'payment_status' => $visit->payment_status,
                'doctor_name' => $visit->doctor?->name,
                'doctor_id' => $visit->doctor?->id,
            ],
            'vitals' => $vitals,
            'recentMedicines' => $recentMedicines,
            'pendingTests' => [],
            'history' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->all();

        DB::beginTransaction();

        try {
            $consultation = Consultation::create([
                'visit_id'              => $validated['visit_id'],
                'patient_id'            => $validated['patient_id'],
                'doctor_id'             => $validated['doctor_id'],
                'chief_complaint'       => $validated['chief_complaint'] ?? null,
                'history'               => $validated['history'] ?? null,
                'examination_notes'     => $validated['examination_notes'] ?? null,
                'diagnosis'             => $validated['diagnosis'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'follow_up_date'        => $validated['follow_up_date'] ?? null,
                'consultation_status'   => 'completed',
                'consulted_at'          => now(),
                'created_by'            => auth()->id(),
            ]);

            if ($request->hasFile('files')) {
                $paths = [];
                foreach ($request->file('files') as $file) {
                    $path = $file->store('consultations', 'public');
                    $paths[] = $path;
                }
                if (!empty($paths)) {
                    $consultation->update(['document_path' => json_encode($paths)]);
                }
            }

            Vital::create([
                'consultation_id' => $consultation->id,
                'patient_id' => $validated['patient_id'],
                'appointment_id' => $validated['appointment_id'] ?? null,
                'visit_id' => $validated['visit_id'],
                'blood_pressure' => $validated['blood_pressure'] ?? null,
                'pulse' => $validated['pulse'] ?? null,
                'temperature' => $validated['temperature'] ?? null,
                'oxygen' => $validated['oxygen'] ?? null,
                'height' => $validated['height'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if (isset($validated['medicines']) && is_array($validated['medicines'])) {
                $prescription = Prescription::create([
                    'consultation_id' => $consultation->id,
                    'patient_id' => $consultation->patient_id,
                    'doctor_id' => $consultation->doctor_id,
                    'prescription_number' => Prescription::generatePrescriptionNumber(),
                    'advices' => $validated['advice'] ?? null,
                    'prescribed_at' => now(),
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['medicines'] as $medicine) {
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $medicine['medicine_id'] ?? null,
                        'medicine_name' => $medicine['medicine_name'] ?? null,
                        'dosage_instruction' => $medicine['dosage'] ?? $medicine['dosage_instruction'] ?? null,
                        'frequency' => $medicine['frequency'] ?? null,
                        'duration_days' => $medicine['duration_days'] ?? $medicine['duration'] ?? null,
                        'instructions' => $medicine['instruction'] ?? $medicine['instructions'] ?? null,
                        'quantity_prescribed' => $medicine['quantity_prescribed'] ?? $medicine['quantity'] ?? null,
                    ]);
                }
            }

            Visit::where('id', $validated['visit_id'])->update(['status' => 'completed']);

            if (!empty($validated['follow_up_date'])) {
                FollowUp::create([
                    'patient_id' => $consultation->patient_id,
                    'doctor_id' => $consultation->doctor_id,
                    'visit_id' => $consultation->visit_id,
                    'consultation_id' => $consultation->id,
                    'follow_up_date' => $validated['follow_up_date'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
            }

            if (!empty($validated['tests']) && is_array($validated['tests'])) {
                $datePart = now()->format('Ymd');
                $todayCount = LabOrder::whereDate('created_at', today())->count();
                $orderNumber = 'LAB-' . $datePart . '-' . str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);

                $labOrder = LabOrder::create([
                    'consultation_id' => $consultation->id,
                    'patient_id' => $consultation->patient_id,
                    'doctor_id' => $consultation->doctor_id,
                    'order_number' => $orderNumber,
                    'status' => 'ordered',
                    'notes' => $validated['lab_notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['tests'] as $testName) {
                    $labTest = LabTest::firstOrCreate(
                        ['name' => $testName],
                        [
                            'code' => str($testName)->slug('_')->toString(),
                            'price' => 0,
                            'is_active' => true,
                            'created_by' => auth()->id(),
                        ]
                    );
                    LabTestParameter::firstOrCreate(
                        [
                            'lab_test_id' => $labTest->id,
                            'name' => 'Value',
                        ],
                        [
                            'unit' => null,
                            'reference_range' => null,
                            'display_order' => 1,
                            'is_active' => true,
                        ]
                    );
                    LabOrderItem::create([
                        'lab_order_id' => $labOrder->id,
                        'lab_test_id' => $labTest->id,
                        'status' => 'ordered',
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('consultations.show', $consultation->id)
                ->with('success', 'Consultation saved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Consultation store failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to save consultation: ' . $e->getMessage());
        }
    }
}
