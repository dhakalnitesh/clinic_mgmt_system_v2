<?php

namespace App\Http\Controllers\Due;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Patient\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DueController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['patient', 'payments', 'items'])
            ->whereIn('status', ['pending', 'partial']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $dues = $query->latest()->paginate($request->per_page ?? 10)->withQueryString();

        // Calculate summary stats
        $totalDue = Invoice::whereIn('status', ['pending', 'partial'])->sum('total');
        $totalPaidSoFar = Invoice::whereIn('status', ['partial'])->withSum('payments', 'amount')->get()->sum('payments_sum_amount');
        $overdueCount = Invoice::whereIn('status', ['pending', 'partial'])
            ->whereDate('created_at', '<', now()->subDays(30))
            ->count();

        return Inertia::render('Dues/Index', [
            'dues' => $dues,
            'patients' => Patient::orderBy('name')->get(['id', 'name', 'phone']),
            'filters' => $request->only(['search', 'start_date', 'end_date', 'per_page']),
            'stats' => [
                'total_due' => $totalDue - $totalPaidSoFar,
                'pending_invoices' => Invoice::where('status', 'pending')->count(),
                'partial_invoices' => Invoice::where('status', 'partial')->count(),
                'overdue_count' => $overdueCount,
            ],
        ]);
    }
}
