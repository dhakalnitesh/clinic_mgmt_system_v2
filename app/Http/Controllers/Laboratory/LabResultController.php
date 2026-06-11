<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\Laboratory\LabOrder;
use App\Models\Laboratory\LabResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class LabResultController extends Controller
{
    public function index(Request $request)
    {
        $query = LabOrder::query()
            ->with([
                'patient',
                'doctor',
                'items.labTest.parameters',
                'items.results',
            ])
            ->whereHas('items.results');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn ($p) =>
                        $p->where('name', 'like', "%{$search}%")
                          ->orWhere('uhid', 'like', "%{$search}%")
                    )
                    ->orWhereHas('doctor', fn ($d) =>
                        $d->where('name', 'like', "%{$search}%")
                    );
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = $request->integer('per_page', 10);
        $orders = $query->latest()->paginate($perPage)->withQueryString();

        return Inertia::render('Laboratory/Results/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'from_date', 'to_date', 'per_page']),
        ]);
    }

    public function create(LabOrder $labOrder)
    {
        $labOrder->load([
            'patient',
            'doctor',
            'items.labTest.parameters',
        ]);

        return Inertia::render('Laboratory/Results/Create', [
            'labOrder' => $labOrder,
        ]);
    }

    public function store(Request $request, LabOrder $labOrder)
    {
        $validated = $request->validate([
            'results' => ['required', 'array'],
            'results.*.lab_order_item_id' => ['required', 'exists:lab_order_items,id'],
            'results.*.lab_test_parameter_id' => ['required', 'exists:lab_test_parameters,id'],
            'results.*.result_value' => ['nullable', 'string'],
            'results.*.remarks' => ['nullable', 'string'],
        ]);

        $orderItemIds = $labOrder->items()->pluck('id')->toArray();

        foreach ($validated['results'] as $result) {
            if (!in_array($result['lab_order_item_id'], $orderItemIds)) {
                return redirect()
                    ->back()
                    ->with('error', 'Invalid lab order item specified.');
            }

            LabResult::updateOrCreate(
                [
                    'lab_order_item_id' => $result['lab_order_item_id'],
                    'lab_test_parameter_id' => $result['lab_test_parameter_id'],
                ],
                [
                    'result_value' => $result['result_value'],
                    'remarks' => $result['remarks'] ?? null,
                ]
            );
        }

        return redirect()
            ->route('laboratory.orders.show', $labOrder)
            ->with('success', 'Results saved successfully.');
    }

    public function print(LabOrder $labOrder)
    {
        $labOrder->load([
            'patient',
            'doctor',
            'items.labTest.parameters',
            'items.results.parameter',
        ]);

        return Inertia::render('Laboratory/Results/Print', [
            'labOrder' => $labOrder,
        ]);
    }

    public function exportCsv(LabOrder $labOrder)
    {
        $labOrder->load([
            'patient',
            'doctor',
            'items.labTest.parameters',
            'items.results.parameter',
        ]);

        $callback = function () use ($labOrder) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Order Number', 'Patient Name', 'Patient UHID',
                'Doctor', 'Test', 'Parameter', 'Result',
                'Unit', 'Reference Range', 'Date',
            ]);

            foreach ($labOrder->items as $item) {
                foreach ($item->labTest->parameters as $param) {
                    $result = $item->results
                        ->where('lab_test_parameter_id', $param->id)
                        ->first();

                    fputcsv($handle, [
                        $labOrder->order_number,
                        $labOrder->patient?->name,
                        $labOrder->patient?->uhid,
                        $labOrder->doctor?->name,
                        $item->labTest->name,
                        $param->name,
                        $result?->result_value ?? '',
                        $param->unit ?? '',
                        $param->reference_range ?? '',
                        $labOrder->created_at->format('Y-m-d'),
                    ]);
                }
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="lab-results-' . $labOrder->order_number . '.csv"',
        ]);
    }

    public function exportPdf(LabOrder $labOrder)
    {
        $labOrder->load([
            'patient',
            'doctor',
            'items.labTest.parameters',
            'items.results.parameter',
        ]);

        $pdf = Pdf::loadView('pdf.lab-result', [
            'labOrder' => $labOrder,
        ]);

        return $pdf->download('lab-results-' . $labOrder->order_number . '.pdf');
    }
}
