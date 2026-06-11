<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\DrugInteraction;
use App\Models\Pharmacy\Generic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DrugInteractionController extends Controller
{
    public function index(Request $request): Response
    {
        $interactions = DrugInteraction::query()
            ->with(['generic1', 'generic2'])
            ->when($request->search, fn ($q) =>
                $q->whereHas('generic1', fn ($s) => $s->where('name', 'like', "%{$request->search}%"))
                  ->orWhereHas('generic2', fn ($s) => $s->where('name', 'like', "%{$request->search}%"))
            )
            ->when($request->severity, fn ($q) => $q->where('severity', $request->severity))
            ->orderBy('severity')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Pharmacy/DrugInteractions/Index', [
            'interactions' => $interactions,
            'generics'     => Generic::active()->orderBy('name')->get(['id', 'name']),
            'filters'      => $request->only(['search', 'severity']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pharmacy/DrugInteractions/Create', [
            'generics' => Generic::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'generic_id_1' => ['required', 'exists:generics,id', 'different:generic_id_2'],
            'generic_id_2' => ['required', 'exists:generics,id', 'different:generic_id_1'],
            'severity'     => ['required', 'in:minor,moderate,major,contraindicated'],
            'description'  => ['required', 'string', 'max:500'],
            'management'   => ['nullable', 'string', 'max:500'],
            'reference'    => ['nullable', 'string', 'max:200'],
            'is_active'    => ['boolean'],
        ]);

        DrugInteraction::create($validated);

        return redirect()
            ->route('pharmacy.drug-interactions.index')
            ->with('success', 'Drug interaction created.');
    }

    public function edit(DrugInteraction $drugInteraction): Response
    {
        return Inertia::render('Pharmacy/DrugInteractions/Edit', [
            'interaction' => $drugInteraction,
            'generics'    => Generic::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, DrugInteraction $drugInteraction)
    {
        $validated = $request->validate([
            'generic_id_1' => ['required', 'exists:generics,id', 'different:generic_id_2'],
            'generic_id_2' => ['required', 'exists:generics,id', 'different:generic_id_1'],
            'severity'     => ['required', 'in:minor,moderate,major,contraindicated'],
            'description'  => ['required', 'string', 'max:500'],
            'management'   => ['nullable', 'string', 'max:500'],
            'reference'    => ['nullable', 'string', 'max:200'],
            'is_active'    => ['boolean'],
        ]);

        $drugInteraction->update($validated);

        return redirect()
            ->route('pharmacy.drug-interactions.index')
            ->with('success', 'Drug interaction updated.');
    }

    public function destroy(DrugInteraction $drugInteraction)
    {
        $drugInteraction->delete();

        return redirect()
            ->route('pharmacy.drug-interactions.index')
            ->with('success', 'Drug interaction deleted.');
    }
}
