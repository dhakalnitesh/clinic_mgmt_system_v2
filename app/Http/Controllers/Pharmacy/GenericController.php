<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Generic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GenericController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Pharmacy/Settings/Generics', [
            'generics' => Generic::withCount('medicines')
                ->orderBy('name')
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:150|unique:generics,name',
            'pharmacological_class'=> 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'is_controlled'       => 'boolean',
            'is_active'           => 'boolean',
        ]);
        Generic::create($request->validated());
        return back()->with('success', 'Generic created.');
    }

    public function update(Request $request, Generic $generic)
    {
        $request->validate([
            'name'                => "required|string|max:150|unique:generics,name,{$generic->id}",
            'pharmacological_class'=> 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'is_controlled'       => 'boolean',
            'is_active'           => 'boolean',
        ]);
        $generic->update($request->validated());
        return back()->with('success', 'Generic updated.');
    }

    public function destroy(Generic $generic)
    {
        if ($generic->medicines()->exists()) {
            return back()->with('error', 'Cannot delete generic with medicines assigned.');
        }
        $generic->delete();
        return back()->with('success', 'Generic deleted.');
    }
}