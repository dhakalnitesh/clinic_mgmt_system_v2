<?php

namespace App\Http\Controllers\Visit;
use App\Http\Controllers\Controller;

use App\Http\Requests\StoreVisitRequest;
use App\Models\Appointment\Appointment;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\Visit\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $visits = Visit::with(['patient', 'doctor', 'appointment'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('doctor', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('chief_complaint', 'like', "%{$search}%");
            })
            ->when($request->start_date, function ($query, $date) {
                $query->whereDate('visited_at', '>=', $date);
            })
            ->when($request->end_date, function ($query, $date) {
                $query->whereDate('visited_at', '<=', $date);
            })
            ->when($request->visit_type, function ($query, $type) {
                if ($type === 'walk_in') {
                    $query->whereNull('appointment_id');
                } elseif ($type === 'appointment') {
                    $query->whereNotNull('appointment_id');
                }
            })
            ->orderBy('visited_at', 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::orderBy('name')->get();
        $appointments = Appointment::where('status', 'visited')
            ->orderBy('appointment_date', 'desc')
            ->get();

        return Inertia::render('Visits/Index', [
            'visits' => $visits,
            'patients' => $patients,
            'doctors' => $doctors,
            'appointments' => $appointments,
            'filters' => $request->only(['search', 'start_date', 'end_date', 'per_page', 'visit_type']),
        ]);
    }


    //need to update hare late brooo
//      public function index()
// {
//     $todayDay = Carbon::now()->format('l');
//     $currentTime = Carbon::now()->format('H:i:s');

//     $visits = Visit::with(['patient', 'doctor', 'appointment'])
//         ->orderBy('visited_at', 'desc')
//         ->get();

//     /**
//      * Only doctors available TODAY
//      * based on schedule
//      */
//     $doctors = Doctor::whereHas('schedules', function ($query) use ($todayDay) {

//             $query->where('day', $todayDay);

//         })
//         ->with(['schedules' => function ($query) use ($todayDay, $currentTime) {

//             $query->where('day', $todayDay)
//                   ->where(function ($q) use ($currentTime) {
//                       $q->whereNull('start_time')
//                         ->orWhere('start_time', '<=', $currentTime);
//                   })
//                   ->where(function ($q) use ($currentTime) {
//                       $q->whereNull('end_time')
//                         ->orWhere('end_time', '>=', $currentTime);
//                   });

//         }])
//         ->orderBy('name')
//         ->get();

//     /**
//      * Only TODAY appointments with status visited
//      */
//     $appointments = Appointment::where('status', 'visited')
//         ->orderBy('appointment_date', 'desc')
//         ->get();

//     $patients = Patient::orderBy('name')->get();

//     return Inertia::render('Visits/Index', [
//         'visits' => $visits,
//         'patients' => $patients,
//         'doctors' => $doctors,
//         'appointments' => $appointments,
//         'todayDay' => $todayDay,
//     ]);
// }
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            // have some problem with this validation 
            // 'new_patient.name' => 'required_without:patient_id|string|max:255',
            // 'new_patient.phone' => 'required_without:patient_id|string|max:20',
            // 'new_patient.address1' => 'nullable|string|max:255',
            // 'consultation_fee' => 'nullable',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visited_at' => 'nullable',
            'chief_complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'visit_type' => 'nullable',
            'notes' => 'nullable|string',
        ]);
        // dd($validated);
        try {

            DB::transaction(function () use ($validated) {

                $patientId = $validated['patient_id'] ?? null;

                if (!$patientId) {

                    $patient = Patient::create([
                        'name' => $validated['new_patient']['name'],
                        'phone' => $validated['new_patient']['phone'],
                        'address1' => $validated['new_patient']['address1'] ?? null,
                    ]);
                    $patientId = $patient->id;
                }
                // dd($patientId);
                // dd($validated);
                $exists = Visit::where('patient_id', $validated['patient_id'])
                    ->where('doctor_id', $validated['doctor_id'])
                    ->whereDate('visited_at', $validated['visited_at'])
                    ->exists();

                if ($exists) {
                    return back()->withErrors([
                        'patient_id' => 'This patient already has an appointment with this doctor on this date.'
                    ])->withInput();
                }
                Visit::create([
                    'patient_id' => $patientId,
                    'doctor_id' => $validated['doctor_id'],
                    'appointment_id' => $validated['appointment_id'] ?? null,
                    'consultation_fee' => $validated['consultation_fee'] ?? null,
                    'visited_at' => $validated['visited_at'] ?? now(),


                    'chief_complaint' => $validated['chief_complaint'] ?? null,
                    'diagnosis' => $validated['diagnosis'] ?? null,
                    'notes' => $validated['notes'] ?? null,

                    'status' => 'waiting',
                ]);
            });

            return redirect()
                ->back()
                ->with('success', 'Visit created successfully.');
        } catch (\Throwable $e) {

            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cancel(Visit $visit)
    {
        // dd($visit->all());

        $visit->update([
            'status' => 'cancelled'
        ]);

        return back()->with('success', 'Appointment cancelled');
    }

    public function update(Request $request, $id)
    {
        // dd($request->all()); 
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',

            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'status' => 'nullable',
            'consultation_fee' => 'nullable',

            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $visit = Visit::findOrFail($id);
        $visit->update([
            'status' => $validated['status'],
        ]);
        return redirect()
            ->back()
            ->with('success', 'Visit updated successfully.');
    }
    // public function update(Request $request, $id)
    // {
    //     $validated = $request->validate([
    //         'patient_id' => 'nullable|exists:patients,id',

    //         'doctor_id' => 'required|exists:doctors,id',
    //         'appointment_id' => 'nullable|exists:appointments,id',

    //         'symptoms' => 'nullable|string',
    //         'diagnosis' => 'nullable|string',
    //         'notes' => 'nullable|string',
    //     ]);

    //     try {

    //         DB::transaction(function () use ($validated, $id) {

    //             $visit = Visit::findOrFail($id);

    //             $patientId = $validated['patient_id'] ?? null;

    //             /**
    //              * CREATE NEW PATIENT IF NOT SELECTED
    //              */
    //             if (!$patientId && isset($validated['new_patient'])) {

    //                 $patient = Patient::create([
    //                     'name' => $validated['new_patient']['name'],
    //                     'phone' => $validated['new_patient']['phone'],
    //                     'address1' => $validated['new_patient']['address1'] ?? null,
    //                 ]);

    //                 $patientId = $patient->id;
    //             }

    //             /**
    //              * UPDATE VISIT
    //              */
    //             $visit->update([
    //                 'patient_id' => $patientId,
    //                 'doctor_id' => $validated['doctor_id'],
    //                 'appointment_id' => $validated['appointment_id'] ?? null,

    //                 'symptoms' => $validated['symptoms'] ?? null,
    //                 'diagnosis' => $validated['diagnosis'] ?? null,
    //                 'notes' => $validated['notes'] ?? null,
    //             ]);
    //         });

    //         return redirect()
    //             ->back()
    //             ->with('success', 'Visit updated successfully.');
    //     } catch (\Throwable $e) {

    //         return back()->withErrors([
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }

    // public function destroy(Visit $visit)
    // {
    // $visit->delete();
    // }
}
