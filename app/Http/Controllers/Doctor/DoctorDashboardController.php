<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Consultation\Consultation;
use App\Models\Laboratory\LabResult;
use App\Models\Patient\Patient;
use App\Models\Doctor\Doctor;


use App\Models\Visit\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorDashboardController extends Controller
{
    public function index()
    {
        $doctor = auth()->user();
        $today = Carbon::today();

        // --- Stats ---
        $todayAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->count();

        $remainingAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->where('status', 'scheduled')
            ->count();

        $waitingPatients = Visit::where('doctor_id', $doctor->id)
            ->whereDate('visited_at', $today)
            ->where('status', 'waiting')
            ->count();

        $consultationsToday = Consultation::where('doctor_id', $doctor->id)
            ->whereDate('created_at', $today)
            ->count();

        // $pendingLabs = LabResult::where('doctor_id', $doctor->id)
        //     ->where('reviewed', false)
        //     ->count();

        $pendingLabs=null;
        $followupsDue = Consultation::where('doctor_id', $doctor->id)
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<=', $today)
            ->where('consultation_status', '!=', 'completed') // exclude completed follow-ups
            ->count();

        $overdueFollowupsCount = Consultation::where('doctor_id', $doctor->id)
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<', $today)
            ->where('consultation_status', '!=', 'completed')
            ->count();

        $totalPatients = Patient::all();

        // --- Queue ---
        $queue = Visit::with('patient')
            ->where('doctor_id', $doctor->id)
            ->whereDate('visited_at', $today)
            ->where('status', 'waiting')
            ->orderBy('created_at')
            ->take(5)
            ->get()
            ->map(fn ($visit) => [
                'id' => $visit->id,
                'patient_name' => $visit->patient->name,
                'visit_time' => optional($visit->created_at)->format('h:i A'),
                'status' => $visit->status,
            ]);

        // --- Followups ---
        $followups = Consultation::with('patient')
            ->where('doctor_id', $doctor->id)
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<=', now()->addDays(7))
            ->where('consultation_status', '!=', 'completed')
            ->orderBy('follow_up_date')
            ->take(5)
            ->get()
            ->map(function ($consultation) {
                $followupDate = Carbon::parse($consultation->follow_up_date);

                $urgency = 'upcoming';
                $urgencyLabel = 'Upcoming';

                if ($followupDate->isPast() && !$followupDate->isToday()) {
                    $urgency = 'overdue';
                    $urgencyLabel = 'Overdue';
                } elseif ($followupDate->isToday()) {
                    $urgency = 'today';
                    $urgencyLabel = 'Today';
                }

                return [
                    'id' => $consultation->id,
                    'patient_name' => $consultation->patient->name,
                    'type' => 'Consultation Follow-up',
                    'due_label' => $followupDate->diffForHumans(),
                    'urgency' => $urgency,
                    'urgency_label' => $urgencyLabel,
                ];
            });

        // --- Recent Prescriptions ---
        // $recentPrescriptions = Prescription::with('patient')
        //     ->where('doctor_id', $doctor->id)
        //     ->latest()
        //     ->take(5)
        //     ->get()
        //     ->map(fn ($rx) => [
        //         'id' => $rx->id,
        //         'patient_name' => $rx->patient->name,
        //         'drugs_summary' => $rx->medication ?? 'Prescription',
        //         'created_at_label' => $rx->created_at->diffForHumans(),
        //     ]);
$recentPrescriptions=null;
        // --- Pending Labs Alert ---
        // $pendingLabsAlert = LabResult::with('patient')
        //     ->where('doctor_id', $doctor->id)
        //     ->where('reviewed', false)
        //     ->latest()
        //     ->take(5)
        //     ->get()
        //     ->map(fn ($lab) => [
        //         'id' => $lab->id,
        //         'patient_name' => $lab->patient->name,
        //     ]);
 $pendingLabsAlert=1;
        // --- Overdue Followups Alert ---
        $overdueFollowups = Consultation::with('patient')
            ->where('doctor_id', $doctor->id)
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<', $today)
            ->where('consultation_status', '!=', 'completed')
            ->take(5)
            ->get()
            ->map(fn ($consultation) => [
                'id' => $consultation->id,
                'patient_name' => $consultation->patient->name,
            ]);

        return Inertia::render('Doctors/Dashboard', [
            'stats' => [
                'todayAppointments' => $todayAppointments,
                'remainingAppointments' => $remainingAppointments,
                'waitingPatients' => $waitingPatients,
                'avgWaitMinutes' => 15, // can be calculated dynamically if queue timestamps exist
                'consultationsToday' => $consultationsToday,
                'pendingLabs' => $pendingLabs,
                'followupsDue' => $followupsDue,
                'overdueFollowups' => $overdueFollowupsCount,
                'totalPatients' => $totalPatients,
                'pendingConsultations' => $remainingAppointments,
                'labResultsToday' => LabResult::whereDate('created_at', $today)->count(),
            ],
            'queue' => $queue,
            'followups' => $followups,
            'recentPrescriptions' => $recentPrescriptions,
            'pendingLabsAlert' => $pendingLabsAlert,
            'overdueFollowups' => $overdueFollowups,
        ]);
    }
}