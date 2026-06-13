<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\PatientRequest;
use App\Models\Address\Province;
use App\Models\Address\District;
use App\Models\Address\Municipal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PatientController extends Controller
{
    public function create(Request $request)
    {
        $alreadyCreated = $request->session()->get('guest_patient_created', false);
        $patient = null;

        if ($alreadyCreated) {
            $patientId = $request->session()->get('guest_patient_id');
            $patient = $patientId ? \App\Models\Patient\Patient::find($patientId) : null;
        }

        return Inertia::render('Guest/Patients/Create', [
            'provinces' => Province::all(),
            'districts' => District::all(),
            'municipals' => Municipal::all(),
            'alreadyCreated' => $alreadyCreated,
            'patient' => $patient,
        ]);
    }

    public function store(PatientRequest $request)
    {
        $data = $request->validated();
        $patient = \App\Models\Patient\Patient::create($data);

        $request->session()->put('guest_patient_created', true);
        $request->session()->put('guest_patient_id', $patient->id);

        return redirect()->route('guest.patients.create')->with([
            'success' => 'Patient registered successfully! You can now book an appointment.',
        ]);
    }
}
