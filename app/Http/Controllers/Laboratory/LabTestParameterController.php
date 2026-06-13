<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\Laboratory\LabTest;
use App\Models\Laboratory\LabTestParameter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LabTestParameterController extends Controller
{
   public function index(Request $request)
{
    $perPage = $request->integer('per_page', 15);

    $parameters = LabTestParameter::query()
        ->with('labTest:id,name')

        ->when(
            $request->filled('search'),
            fn ($q) =>
            $q->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhereHas('labTest', function ($labTest) use ($request) {
                        $labTest->where('name', 'like', '%' . $request->search . '%');
                    });
            })
        )

        ->when(
            $request->filled('status'),
            fn ($q) =>
            $q->where('is_active', $request->status)
        )

        ->when(
            $request->filled('from_date'),
            fn ($q) =>
            $q->whereDate('created_at', '>=', $request->from_date)
        )

        ->when(
            $request->filled('to_date'),
            fn ($q) =>
            $q->whereDate('created_at', '<=', $request->to_date)
        )

        ->latest()
        ->paginate($perPage)
        ->withQueryString();

    return Inertia::render('Laboratory/TestParameters/Index', [
        'parameters' => $parameters,

        'labTests' => LabTest::active()
            ->orderBy('name')
            ->get(['id', 'name']),

        'filters' => [
            'search' => $request->search,
            'status' => $request->status,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'per_page' => $perPage,
        ],
    ]);
}

    public function create()
    {
        return Inertia::render(
            'Laboratory/TestParameters/Create',
            [
                'labTests' => LabTest::active()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lab_test_id' => ['required', 'exists:lab_tests,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:100'],
            'reference_range' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        LabTestParameter::create($validated);

        return redirect()
            ->route('laboratory.test-parameters.index')
            ->with('success', 'Parameter created successfully.');
    }

    public function edit(LabTestParameter $testParameter)
    {
        return Inertia::render(
            'Laboratory/TestParameters/Edit',
            [
                'parameter' => $testParameter,

                'labTests' => LabTest::active()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),
            ]
        );
    }

    public function update(
        Request $request,
        LabTestParameter $testParameter
    ) {
        $validated = $request->validate([
            'lab_test_id' => ['required', 'exists:lab_tests,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:100'],
            'reference_range' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $testParameter->update($validated);

        return redirect()
            ->route('laboratory.test-parameters.index')
            ->with('success', 'Parameter updated successfully.');
    }

    public function destroy(LabTestParameter $testParameter)
    {
        $testParameter->delete();

        return back()->with(
            'success',
            'Parameter deleted successfully.'
        );
    }
}
