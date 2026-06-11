<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\Laboratory\LabOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LabOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = LabOrder::query()
            ->with([
                'patient',
                'doctor',
            ])
            ->withCount('items');

        // Search
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('order_number', 'like', "%{$search}%")

                    ->orWhereHas('patient', function ($patient) use ($search) {

                        $patient->where('name', 'like', "%{$search}%")
                            ->orWhere('uhid', 'like', "%{$search}%");
                    })

                    ->orWhereHas('doctor', function ($doctor) use ($search) {

                        $doctor->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date Range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = $request->integer('per_page', 10);

        $orders = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Laboratory/Orders/Index', [
            'orders' => $orders,

            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(LabOrder $labOrder)
    {
        $labOrder->load([
            'patient',
            'doctor',
            'consultation',
            'items.labTest',
        ]);

        return Inertia::render(
            'Laboratory/Orders/Show',
            [
                'labOrder' => $labOrder,
            ]
        );
    }

    public function collectSample(LabOrder $labOrder)
    {
        $labOrder->update([
            'status' => 'sample_collected',
        ]);

        return back()->with(
            'success',
            'Sample collected successfully.'
        );
    }

    public function startProcessing(LabOrder $labOrder)
{
    $labOrder->update([
        'status' => 'processing',
    ]);

    return back()->with(
        'success',
        'Order moved to processing.'
    );
}
public function complete(LabOrder $labOrder)
{
    $labOrder->update([
        'status' => 'completed',
    ]);

    return back()->with(
        'success',
        'Order completed successfully.'
    );
}
}
