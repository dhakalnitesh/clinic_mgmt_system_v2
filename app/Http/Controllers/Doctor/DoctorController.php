<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor\Doctor;
use App\Models\Doctor\DoctorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $doctors = Doctor::with('schedules')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('nmc_number', 'like', "%{$search}%")
                        ->orWhere('specialization', 'like', "%{$search}%");
                });
            })
            ->when($request->start_date, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->end_date, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('Doctors/Index', [
            'doctors' => $doctors,
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
            ]
        ]);
    }

    public function show(Doctor $doctor)
    {
        $doctor->load([
            'schedules',
            'consultations.patient',
            'prescriptions.patient',
            'labOrders.patient',
            'followUps.patient',
            'visits.patient',
            'appointments.patient',
        ]);

        return Inertia::render('Doctors/Show', [
            'doctor' => $doctor,
        ]);
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'nmc_number' => ['required', 'string'],
            'phone' => ['required', 'digits:10', 'regex:/^[0-9]+$/'],
            'specialization' => ['nullable', 'string'],
            'consultation_fee' => ['nullable', 'numeric'],
            'address1' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'doctor_schedule' => ['nullable', 'array'],
            'doctor_schedule.*.day' => ['required_with:doctor_schedule', 'string'],
            'doctor_schedule.*.start_time' => ['nullable', 'date_format:H:i'],
            'doctor_schedule.*.end_time' => ['nullable', 'date_format:H:i'],

        ]);

        try {

            DB::transaction(function () use ($request, $data) {
                $photoPath = null;

                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('doctors', 'public');
                }
                $doctor = Doctor::create([
                    'name' => $data['name'],
                    'nmc_number' => $data['nmc_number'],
                    'phone' => $data['phone'],
                    'specialization' => $data['specialization'] ?? null,
                    'consultation_fee' => $data['consultation_fee'] ?? null,
                    'address1' => $data['address1'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'photo' => $photoPath,
                ]);

                if (!empty($data['doctor_schedule'])) {

                    $doctor->schedules()->createMany(
                        collect($data['doctor_schedule'])->map(function ($schedule) {
                            return [
                                'day' => $schedule['day'],
                                'start_time' => $schedule['start_time'] ?? null,
                                'end_time' => $schedule['end_time'] ?? null,

                            ];
                        })->toArray()
                    );
                }
            });

            return redirect()
                ->back()
                ->with('success', 'Doctor created successfully.');
        } catch (\Throwable $e) {

            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'nmc_number' => ['required', 'string'],
            'phone' => ['required', 'digits:10', 'regex:/^[0-9]+$/'],
            'specialization' => ['nullable', 'string'],
            'consultation_fee' => ['nullable', 'numeric'],
            'address1' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'doctor_schedule' => ['nullable', 'array'],
            'doctor_schedule.*.day' => ['required_with:doctor_schedule', 'string'],
            'doctor_schedule.*.start_time' => ['nullable', 'date_format:H:i'],
            'doctor_schedule.*.end_time' => ['nullable', 'date_format:H:i'],
        ]);

        try {

            DB::transaction(function () use ($request, $data, $id) {

                $doctor = Doctor::findOrFail($id);

                $photoPath = $doctor->photo;

                if ($request->hasFile('photo')) {

                    // delete old photo if exists
                    if ($doctor->photo && Storage::disk('public')->exists($doctor->photo)) {
                        Storage::disk('public')->delete($doctor->photo);
                    }

                    $photoPath = $request->file('photo')->store('doctors', 'public');
                }
                $doctor->update([
                    'name' => $data['name'],
                    'nmc_number' => $data['nmc_number'],
                    'phone' => $data['phone'],
                    'specialization' => $data['specialization'] ?? null,
                    'consultation_fee' => $data['consultation_fee'] ?? null,
                    'address1' => $data['address1'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'photo' => $photoPath,
                ]);

                /**
                 * REPLACE SCHEDULE (SYNC APPROACH)
                 */
                DoctorSchedule::where('doctor_id', $doctor->id)->delete();

                if (!empty($data['doctor_schedule'])) {

                    $doctor->schedules()->createMany(
                        collect($data['doctor_schedule'])->map(function ($schedule) {
                            return [
                                'day' => $schedule['day'],
                                'start_time' => $schedule['start_time'] ?? null,
                                'end_time' => $schedule['end_time'] ?? null,
                            ];
                        })->toArray()
                    );
                }
            });

            return redirect()
                ->back()
                ->with('success', 'Doctor updated successfully.');
        } catch (\Throwable $e) {

            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
    }
}
