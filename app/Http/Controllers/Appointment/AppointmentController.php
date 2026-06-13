<?php

namespace App\Http\Controllers\Appointment;
use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\Visit\Visit;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    // public function index()
    // {
    //     return Inertia::render('Appointments/Index', [
    //         'appointments' => Appointment::with(['patient', 'doctor'])
    //             ->latest()
    //             ->get(),
    //         'patients' => Patient::all(),
    //         'doctors' => Doctor::all(),
    //     ]);
    // }
    // for backend filter 
  public function index(Request $request)
{
    if ($request->has('appointment_date')) {

        $adDate = toEnglishDate($request->appointment_date, 'Y-m-d');

        $dayName = Carbon::parse($adDate)->format('l');

        $doctors = Doctor::whereHas('schedules', function ($query) use ($dayName) {

                $query->where('day', $dayName);

            })
            ->orderBy('name')
            ->get();

        return response()->json([
            'doctors' => $doctors
        ]);
    }

    $appointments = Appointment::with(['patient', 'doctor'])
        ->when($request->search, function ($query, $search) {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhereHas('doctor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        })
        ->when($request->start_date, function ($query, $date) {
            $query->whereDate('appointment_date', '>=', $date);
        })
        ->when($request->end_date, function ($query, $date) {
            $query->whereDate('appointment_date', '<=', $date);
        })
        ->latest()
        ->paginate($request->per_page ?? 10)
        ->withQueryString();

    $today = \Carbon\Carbon::today();
    $minDate = toNepaliDate($today->format('Y-m-d'));
    $maxDate = toNepaliDate($today->copy()->addDays(6)->format('Y-m-d'));

    return inertia('Appointments/Index', [
        'appointments' => $appointments,
        'patients' => Patient::all(),
        'doctors' => Doctor::all(),
        'filters' => $request->only(['search', 'start_date', 'end_date', 'per_page']),
        'minDate' => $minDate,
        'maxDate' => $maxDate,
    ]);
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

            Appointment::create($data);
        } catch (UniqueConstraintViolationException $e) {
            return back()->withErrors([
                'appointment_date' => 'Appointment is already booked for this date.',
            ])->withInput();
        }

        return back()->with('success', 'Appointment created');
    }

    // public function update(Request $request, Appointment $appointment)
    // {
    //     // dd($request->all());
    //     $data = $request->validate([
    //         'patient_id' => 'required',
    //         'doctor_id' => 'required',
    //         'appointment_date' => 'required|date',
    //         'appointment_time' => 'nullable',
    //         'status' => 'required',
    //         'reason' => 'nullable',
    //     ]);
    //     // dd($data);
    //   $appointment->update($data);
    // if ($appointment->status === 'cancelled') {
    //     Visit::where('appointment_id', $appointment->id)
    //         ->update([
    //             'status' => 'cancelled'
    //         ]);
    // }

    // if ($appointment->status === 'completed') {
    //     Visit::where('appointment_id', $appointment->id)
    //         ->update([
    //             'status' => 'completed'
    //         ]);
    // }

    //     return back()->with('success', 'Appointment updated');
    // }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:cancelled',
            'reasons' => 'nullable|string',
        ]);

        $appointment = Appointment::findOrFail($id);

        $appointment->update([
            'status' => $request->status,
            'reasons' => $request->reasons,
        ]);

        return back()->with('success', 'Appointment cancelled');
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:waiting,completed,cancelled,visited'
        ]);

        $appointment = Appointment::findOrFail($id);

        $appointment->update([
            'status' => $request->status
        ]);

        if ($request->status === 'visited') {
            $datePrefix = now()->format('ymd');
            $lastVisit = Visit::where('doctor_id', $appointment->doctor_id)
                ->whereDate('created_at', today())
                ->orderByDesc('id')
                ->first();

            if ($lastVisit && $lastVisit->token_number) {
                $parts = explode('-', $lastVisit->token_number);
                $lastSeq = (int) end($parts);
                $seq = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $seq = '001';
            }

            $tokenNumber = 'TKN-' . $appointment->doctor_id . '-' . $datePrefix . '-' . $seq;

            Visit::create([
                'token_number' => $tokenNumber,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'visited_at' => now(),
                'visit_type' => 'appointment',
                'status' => 'waiting',
            ]);
        }

        return back()->with('success', 'Appointment marked as visited.');
    }

    // public function destroy(Appointment $appointment)
    // {
    //     $appointment->delete();

    //     return back()->with('success', 'Appointment deleted');
    // }
}
