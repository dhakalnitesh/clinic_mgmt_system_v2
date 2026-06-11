<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\MedicineCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicineCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Pharmacy/Settings/Categories', [
            'categories' => MedicineCategory::withCount('medicines')
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:medicine_categories,name',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        MedicineCategory::create($validated);
        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, MedicineCategory $category)
    {
        $validated = $request->validate([
            'name'        => "required|string|max:100|unique:medicine_categories,name,{$category->id}",
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $category->update($validated);
        return back()->with('success', 'Category updated.');
    }

    public function destroy(MedicineCategory $category)
    {
        if ($category->medicines()->exists()) {
            return back()->with('error', 'Cannot delete category with medicines assigned.');
        }
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}