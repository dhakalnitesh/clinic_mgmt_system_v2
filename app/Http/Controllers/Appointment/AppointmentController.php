<?php

namespace App\Http\Controllers\Appointment;
use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use Carbon\Carbon;
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

        $dayName = Carbon::parse(
            $request->appointment_date
        )->format('l');

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

    return inertia('Appointments/Index', [
        'appointments' => $appointments,
        'patients' => Patient::all(),
        'doctors' => Doctor::all(),
        'filters' => $request->only(['search', 'start_date', 'end_date', 'per_page']),
    ]);
}
    public function store(Request $request)
    {
        // dd($request->all());
        try {

            $data = $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
                'doctor_id' => ['required', 'exists:doctors,id'],
                'consultation_fee' => 'nullable',
                'appointment_date' => ['required', 'date'],
                'appointment_time' => ['nullable'],
                'status' => ['nullable'],
                'reason' => ['nullable'],
            ]);

            // 🔥 DUPLICATION CHECK
            $exists = Appointment::where('patient_id', $data['patient_id'])
                ->where('doctor_id', $data['doctor_id'])
                ->whereDate('appointment_date', $data['appointment_date'])
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'patient_id' => 'This patient already has an appointment with this doctor on this date.'
                ])->withInput();
            }

            Appointment::create($data);

            return back()->with('success', 'Appointment created');
        } catch (\Throwable $e) {

            return back()->withErrors([
                'error' => 'Something went wrong while creating appointment.'
            ]);
        }
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
            'status' => 'required|in:waiting,completed,cancelled,visited'
        ]);

        $appointment = Appointment::findOrFail($id);

        $appointment->update([
            'status' => $request->status
        ]);
        return back()->with('success', 'Appointment Marked as cancelled');
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

        return back()->with('success', 'Appointment marked as visited.');
    }

    // public function destroy(Appointment $appointment)
    // {
    //     $appointment->delete();

    //     return back()->with('success', 'Appointment deleted');
    // }
}
