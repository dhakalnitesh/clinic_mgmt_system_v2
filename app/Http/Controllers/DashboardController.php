<?php

namespace App\Http\Controllers;

use App\Models\Appointment\Appointment;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\Visit\Visit;
use Carbon\Carbon;
use Inertia\Inertia;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;

class DashboardController extends Controller
{
    private function toNepali(Carbon $date, string $format = 'd F Y'): string
    {
        try {
            return LaravelNepaliDate::from($date->format('Y-m-d'))->toNepaliDate($format);
        } catch (\Throwable) {
            return $date->format('M d');
        }
    }

    public function index()
    {
        $today = Carbon::today();
        $user = request()->user();

        $todayAppointments = Appointment::whereDate('appointment_date', $today)->count();
        $todayPatients = Patient::whereDate('created_at', $today)->count();
        $activeDoctors = Doctor::count();
        $todayVisits = Visit::whereDate('visited_at', $today)->count();

        $isAdmin = $user?->hasRole('admin');

        if ($isAdmin) {
            $weeklyData = collect(range(6, 0))->map(function ($daysAgo) {
                $date = Carbon::today()->subDays($daysAgo);
                $nepaliDate = $this->toNepali($date);
                return [
                    'date' => $date->format('Y-m-d'),
                    'nepali_date' => $nepaliDate,
                    'appointments' => Appointment::whereDate('appointment_date', $date)->count(),
                    'visits' => Visit::whereDate('visited_at', $date)->count(),
                ];
            });

            $monthlyData = collect(range(11, 0))->map(function ($monthsAgo) {
                $date = Carbon::today()->startOfMonth()->subMonths($monthsAgo);
                $end = $date->copy()->endOfMonth();
                $nepaliDate = $this->toNepali($date, 'F Y');
                return [
                    'nepali_date' => $nepaliDate,
                    'appointments' => Appointment::whereBetween('appointment_date', [$date, $end])->count(),
                    'visits' => Visit::whereBetween('visited_at', [$date, $end])->count(),
                ];
            });

            return Inertia::render('Dashboard', [
                'todayAppointments' => $todayAppointments,
                'todayPatients' => $todayPatients,
                'activeDoctors' => $activeDoctors,
                'todayVisits' => $todayVisits,
                'weeklyData' => $weeklyData,
                'monthlyData' => $monthlyData,
            ]);
        }

        $appointmentBooked = session('guest_appointment_created', false);
        $appointmentData = null;

        if ($appointmentBooked) {
            $info = session('guest_appointment');

            if (!$info) {
                $patientId = session('guest_patient_id');
                $latest = $patientId
                    ? Appointment::with('doctor')->where('patient_id', $patientId)->latest()->first()
                    : Appointment::with('doctor')->latest()->first();
                if ($latest) {
                    $info = [
                        'date_np' => $latest->appointment_date,
                        'doctor_name' => $latest->doctor?->name ?? 'N/A',
                    ];
                }
            }

            if ($info) {
                try {
                    $englishDate = toEnglishDate($info['date_np'], 'Y-m-d');
                    $formattedEn = Carbon::parse($englishDate)->format('F j, Y');
                } catch (\Throwable) {
                    $formattedEn = $info['date_np'];
                }
                $appointmentData = [
                    'date_np' => $info['date_np'],
                    'date_en' => $formattedEn,
                    'doctor_name' => $info['doctor_name'],
                ];
            }
        }

        return Inertia::render('UserDashboard', [
            'todayAppointments' => $todayAppointments,
            'todayPatients' => $todayPatients,
            'activeDoctors' => $activeDoctors,
            'appointmentBooked' => $appointmentBooked,
            'appointment' => $appointmentData,
        ]);
    }
}
