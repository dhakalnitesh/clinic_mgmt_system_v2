<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\Laboratory\LabOrder;
use App\Models\Laboratory\LabResult;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LabResultController extends Controller
{
    public function create(LabOrder $labOrder)
{
    $labOrder->load([
        'patient',
        'doctor',
        'items.labTest.parameters',
    ]);

    return Inertia::render(
        'Laboratory/Results/Create',
        [
            'labOrder' => $labOrder,
        ]
    );
}

public function store(
    Request $request,
    LabOrder $labOrder
) {
    $validated = $request->validate([
        'results' => ['required', 'array'],
    ]);

    foreach ($validated['results'] as $result) {

        LabResult::updateOrCreate(
            [
                'lab_order_item_id' =>
                    $result['lab_order_item_id'],

                'lab_test_parameter_id' =>
                    $result['lab_test_parameter_id'],
            ],
            [
                'result_value' =>
                    $result['result_value'],

                'remarks' =>
                    $result['remarks'] ?? null,
            ]
        );
    }

    $labOrder->update([
        'status' => 'completed',
    ]);

    return redirect()
        ->route(
            'laboratory.orders.show',
            $labOrder
        )
        ->with(
            'success',
            'Results saved successfully.'
        );
}
}
