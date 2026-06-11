<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\MedicineUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicineUnitController extends Controller
{
    public function index()
    {
        $units = MedicineUnit::orderBy('name')->paginate(20)->withQueryString();

        return Inertia::render('Pharmacy/Settings/Units', [
            'units' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:50|unique:medicine_units,name',
            'abbreviation' => 'required|string|max:20|unique:medicine_units,abbreviation',
        ]);

        MedicineUnit::create($validated);

        return redirect()->route('pharmacy.units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function update(Request $request, MedicineUnit $unit)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:50|unique:medicine_units,name,' . $unit->id,
            'abbreviation' => 'required|string|max:20|unique:medicine_units,abbreviation,' . $unit->id,
        ]);

        $unit->update($validated);

        return redirect()->route('pharmacy.units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(MedicineUnit $unit)
    {
        $unit->delete();

        return redirect()->route('pharmacy.units.index')
            ->with('success', 'Unit deleted successfully.');
    }
}
