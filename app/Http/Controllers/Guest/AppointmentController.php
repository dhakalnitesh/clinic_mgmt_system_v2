<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function create(Request $request)
    {
        $alreadyCreated = $request->session()->get('guest_appointment_created', false);
        $patientId = $request->session()->get('guest_patient_id');

        if (!$patientId) {
            return redirect()->route('guest.patients.create')
                ->with('error', 'Please register a patient first.');
        }

        $patient = Patient::find($patientId);

        if (!$patient) {
            return redirect()->route('guest.patients.create')
                ->with('error', 'Patient record not found. Please register again.');
        }

        $today = \Carbon\Carbon::today();
        $minDate = toNepaliDate($today->format('Y-m-d'));
        $maxDate = toNepaliDate($today->copy()->addDays(6)->format('Y-m-d'));

        return Inertia::render('Guest/Appointments/Create', [
            'patient' => $patient,
            'doctors' => Doctor::all(),
            'alreadyCreated' => $alreadyCreated,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
        ]);
    }

    public function getDoctorsByDate(Request $request)
    {
        $date = $request->appointment_date;

        if (!$date) {
            return response()->json(['doctors' => Doctor::all()]);
        }

        $adDate = toEnglishDate($date, 'Y-m-d');
        $dayName = Carbon::parse($adDate)->format('l');

        $doctors = Doctor::whereHas('schedules', function ($query) use ($dayName) {
            $query->where('day', $dayName);
        })->orderBy('name')->get();

        return response()->json(['doctors' => $doctors]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'consultation_fee' => 'nullable',
            'appointment_date' => ['required'],
            'appointment_time' => ['nullable'],
            'status' => ['nullable'],
            'reason' => ['nullable'],
        ]);

        try {
            $gregorianDate = toEnglishDate($data['appointment_date'], 'Y-m-d');
            $date = Carbon::parse($gregorianDate);
            $today = Carbon::today();
            $maxDate = $today->copy()->addDays(6);
            if ($date->isBefore($today)) {
                return back()->withErrors([
                    'appointment_date' => 'The appointment date must be today or a future date.',
                ])->withInput();
            }
            if ($date->isAfter($maxDate)) {
                return back()->withErrors([
                    'appointment_date' => 'You can only book appointments up to 6 days from today.',
                ])->withInput();
            }
        } catch (\Exception $e) {
            return back()->withErrors([
                'appointment_date' => 'Invalid date. Please select a valid Nepali date.',
            ])->withInput();
        }

        try {
            $englishDate = toEnglishDate($data['appointment_date'], 'Y-m-d');

            $exists = Appointment::where('patient_id', $data['patient_id'])
                ->where('doctor_id', $data['doctor_id'])
                ->where('appointment_date', $englishDate)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'appointment_date' => 'Appointment is already booked for this date.',
                ])->withInput();
            }

            $appointment = Appointment::create($data);
        } catch (UniqueConstraintViolationException $e) {
            return back()->withErrors([
                'appointment_date' => 'Appointment is already booked for this date.',
            ])->withInput();
        }

        $doctor = $appointment->doctor()->first();

        $request->session()->put('guest_appointment_created', true);
        $request->session()->put('guest_appointment', [
            'date_np' => $appointment->appointment_date,
            'doctor_name' => $doctor?->name ?? 'N/A',
        ]);

        return redirect()->route('guest.appointments.create')->with([
            'success' => 'Appointment booked successfully!',
        ]);
    }
}
