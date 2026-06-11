<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\PatientRequest;
use App\Models\Patient\Patient;
use App\Models\Address\Province;
use App\Models\Address\District;
use App\Models\Address\Municipal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PatientController extends Controller
{
// public function index(Patient $request)
// {
//     // dd($request->all());
//     $patients = Patient::latest()->get();
//     return Inertia::render('Patients/Index', [
//         'patients' => $patients,

    
//     ]);
// }
public function index(Request $request)
{
    $patients = Patient::query()

        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('gender', 'like', "%{$search}%");
            });
        })

        ->when($request->start_date, function ($query, $startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        })

        ->when($request->end_date, function ($query, $endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        })

        ->latest()
        ->paginate($request->per_page ?? 10)
        ->withQueryString();

    return Inertia::render('Patients/Index', [
        'patients' => $patients,
        'provinces' => Province::all(),
        'districts' => District::all(),
        'municipals' => Municipal::all(),

        'filters' => [
            'search' => $request->search,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'per_page' => $request->per_page ?? 10,
        ],

        'can' => [
            'create' => true,
            'edit' => true,
            'delete' => true,
        ],
    ]);
}
public function store(PatientRequest $request)
{
    // dd($request->all());
    $data=Patient::create($request->validated());
// dd($data);
    return back()->with([
        'success' => 'Patient created successfully.'
    ]);
}
public function edit(Patient $patient)
{
    return Inertia::render('Patients/Edit', [
        'patient' => $patient,
    ]);
}

public function show(Patient $patient)
{
    $patient->load([
        'visits.doctor',
        'visits.consultation',
        'visits.vitals',
        'province',
        'district',
        'municipal',
    ]);

    $consultations = \App\Models\Consultation\Consultation::where('patient_id', $patient->id)
        ->with(['doctor', 'visit'])
        ->latest()
        ->get();

    $prescriptions = \App\Models\Pharmacy\Prescription::whereHas('consultation', function ($q) use ($patient) {
        $q->where('patient_id', $patient->id);
    })->with(['consultation.doctor', 'items'])->latest()->get();

    $labOrders = \App\Models\Laboratory\LabOrder::where('patient_id', $patient->id)
        ->with(['doctor', 'items'])
        ->latest()
        ->get();

    $followUps = \App\Models\FollowUp\FollowUp::where('patient_id', $patient->id)
        ->with(['doctor'])
        ->latest()
        ->get();

    return Inertia::render('Patients/Show', [
        'patient' => $patient,
        'consultations' => $consultations,
        'prescriptions' => $prescriptions,
        'labOrders' => $labOrders,
        'followUps' => $followUps,
    ]);
}

public function update(PatientRequest $request, Patient $patient)
{
    $patient->update($request->validated());

    return back()->with([
        'success' => 'Patient updated successfully.'
    ]);
}

public function destroy(Patient $patient)
{
$patient->delete();
}
}
