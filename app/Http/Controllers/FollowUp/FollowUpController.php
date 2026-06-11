<?php

namespace App\Http\Controllers\FollowUp;

use App\Http\Controllers\Controller;
use App\Models\FollowUp\FollowUp;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $query = FollowUp::with(['patient', 'doctor', 'consultation']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('uhid', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('follow_up_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('follow_up_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $followUps = $query->latest()->paginate($request->per_page ?? 10)->withQueryString();

        return Inertia::render('FollowUps/Index', [
            'followUps' => $followUps,
            'filters' => $request->only(['search', 'start_date', 'end_date', 'per_page', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'visit_id' => 'nullable|exists:visits,id',
            'consultation_id' => 'nullable|exists:consultations,id',
            'follow_up_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'pending';

        FollowUp::create($validated);

        return back()->with('success', 'Follow-up created successfully.');
    }

    public function complete(Request $request, FollowUp $followUp)
    {
        $validated = $request->validate([
            'completed_notes' => 'nullable|string',
        ]);

        $followUp->update([
            'status' => 'completed',
            'completed_notes' => $validated['completed_notes'] ?? null,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Follow-up marked as completed.');
    }

    public function cancel(FollowUp $followUp)
    {
        $followUp->update(['status' => 'cancelled']);

        return back()->with('success', 'Follow-up cancelled.');
    }
}
