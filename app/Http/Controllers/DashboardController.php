<?php

namespace App\Http\Controllers;

use App\Models\Appointment\Appointment;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\Visit\Visit;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $todayAppointments = Appointment::whereDate('appointment_date', $today)->count();
        $todayPatients = Patient::whereDate('created_at', $today)->count();
        $activeDoctors = Doctor::count();
        $todayVisits = Visit::whereDate('visited_at', $today)->count();

        $weeklyData = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            return [
                'date' => $date->format('Y-m-d'),
                'appointments' => Appointment::whereDate('appointment_date', $date)->count(),
                'visits' => Visit::whereDate('visited_at', $date)->count(),
            ];
        });

        return Inertia::render('Dashboard', [
            'todayAppointments' => $todayAppointments,
            'todayPatients' => $todayPatients,
            'activeDoctors' => $activeDoctors,
            'todayVisits' => $todayVisits,
            'weeklyData' => $weeklyData,
        ]);
    }
}
