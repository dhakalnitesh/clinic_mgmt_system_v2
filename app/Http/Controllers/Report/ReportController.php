<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Consultation\Consultation;
use App\Models\Patient\Patient;
use App\Models\Visit\Visit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        $totalPatients = Patient::count();
        $totalAppointments = Appointment::count();
        $totalVisits = Visit::count();
        $totalConsultations = Consultation::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingRevenue = Invoice::whereIn('status', ['pending', 'partial'])->sum('total');
        $todayAppointments = Appointment::whereDate('created_at', today())->count();
        $todayVisits = Visit::whereDate('created_at', today())->count();

        return Inertia::render('Reports/Index', [
            'stats' => [
                'totalPatients' => $totalPatients,
                'totalAppointments' => $totalAppointments,
                'totalVisits' => $totalVisits,
                'totalConsultations' => $totalConsultations,
                'totalRevenue' => $totalRevenue,
                'pendingRevenue' => $pendingRevenue,
                'todayAppointments' => $todayAppointments,
                'todayVisits' => $todayVisits,
            ],
        ]);
    }

    public function appointments(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor'])
            ->when($request->start_date, fn($q, $d) => $q->whereDate('appointment_date', '>=', $d))
            ->when($request->end_date, fn($q, $d) => $q->whereDate('appointment_date', '<=', $d))
            ->when($request->status, fn($q, $s) => $q->where('status', $s));

        $appointments = $query->latest()->paginate($request->per_page ?? 10)->withQueryString();

        return Inertia::render('Reports/Appointments', [
            'appointments' => $appointments,
            'filters' => $request->only(['start_date', 'end_date', 'per_page', 'status']),
        ]);
    }

    public function revenue(Request $request)
    {
        $query = Invoice::with(['patient', 'payments'])
            ->when($request->start_date, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->end_date, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->status, fn($q, $s) => $q->where('status', $s));

        $invoices = $query->latest()->paginate($request->per_page ?? 10)->withQueryString();

        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingAmount = Invoice::whereIn('status', ['pending', 'partial'])->sum('total');

        return Inertia::render('Reports/Revenue', [
            'invoices' => $invoices,
            'totals' => ['collected' => $totalRevenue, 'pending' => $pendingAmount],
            'filters' => $request->only(['start_date', 'end_date', 'per_page', 'status']),
        ]);
    }

    public function patients(Request $request)
    {
        $patients = Patient::withCount(['visits'])
            ->when($request->search, function ($q, $s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('uhid', 'like', "%{$s}%");
            })
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('Reports/Patients', [
            'patients' => $patients,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function exportAppointments(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor'])
            ->when($request->start_date, fn($q, $d) => $q->whereDate('appointment_date', '>=', $d))
            ->when($request->end_date, fn($q, $d) => $q->whereDate('appointment_date', '<=', $d))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest();

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Patient', 'Doctor', 'Date', 'Time', 'Status']);

            $query->chunk(100, function ($appointments) use ($handle) {
                foreach ($appointments as $a) {
                    fputcsv($handle, [
                        $a->id,
                        $a->patient?->name ?? 'N/A',
                        $a->doctor?->name ?? 'N/A',
                        $a->appointment_date,
                        $a->appointment_time,
                        $a->status,
                    ]);
                }
            });
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="appointments.csv"');

        return $response;
    }

    public function exportRevenue(Request $request)
    {
        $query = Invoice::with(['patient', 'payments'])
            ->when($request->start_date, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->end_date, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest();

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Invoice No.', 'Patient', 'Total', 'Paid', 'Status', 'Date']);

            $query->chunk(100, function ($invoices) use ($handle) {
                foreach ($invoices as $inv) {
                    fputcsv($handle, [
                        $inv->id,
                        $inv->invoice_number,
                        $inv->patient?->name ?? 'N/A',
                        $inv->total,
                        $inv->payments->sum('amount'),
                        $inv->status,
                        $inv->created_at->format('Y-m-d'),
                    ]);
                }
            });
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="revenue.csv"');

        return $response;
    }

    public function exportPatients(Request $request)
    {
        $query = Patient::withCount(['visits'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('uhid', 'like', "%{$s}%"))
            ->latest();

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'UHID', 'Name', 'Phone', 'Gender', 'Age', 'Visits', 'Registered']);

            $query->chunk(100, function ($patients) use ($handle) {
                foreach ($patients as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->uhid,
                        $p->name,
                        $p->phone,
                        $p->gender,
                        $p->age,
                        $p->visits_count,
                        $p->created_at->format('Y-m-d'),
                    ]);
                }
            });
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="patients.csv"');

        return $response;
    }
}
