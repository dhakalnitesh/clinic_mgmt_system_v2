<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ─── Medicine Categories ───────────────────────────────────────

    public function test_categories_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.categories.index'));

        $response->assertOk();
    }

    public function test_category_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.categories.store'),
            ['name' => 'Antibiotics', 'description' => 'Antibacterial drugs', 'is_active' => true]
        );

        $this->assertDatabaseHas('medicine_categories', ['name' => 'Antibiotics']);
        $response->assertRedirect();
    }

    public function test_category_can_be_updated(): void
    {
        $category = MedicineCategory::create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)->put(
            route('pharmacy.categories.update', $category),
            ['name' => 'Antipyretics', 'description' => 'Fever reducing', 'is_active' => true]
        );

        $this->assertDatabaseHas('medicine_categories', ['id' => $category->id, 'name' => 'Antipyretics']);
        $response->assertRedirect();
    }

    public function test_category_can_be_deleted(): void
    {
        $category = MedicineCategory::create(['name' => 'Vitamins']);

        $response = $this->actingAs($this->user)->delete(
            route('pharmacy.categories.destroy', $category)
        );

        $this->assertDatabaseMissing('medicine_categories', ['id' => $category->id]);
        $response->assertRedirect();
    }

    // ─── Generics ──────────────────────────────────────────────────

    public function test_generics_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.generics.index'));

        $response->assertOk();
    }

    public function test_generic_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.generics.store'),
            ['name' => 'Paracetamol', 'is_active' => true]
        );

        $this->assertDatabaseHas('generics', ['name' => 'Paracetamol']);
        $response->assertRedirect();
    }

    public function test_generic_can_be_updated(): void
    {
        $generic = Generic::create(['name' => 'Old Generic']);

        $response = $this->actingAs($this->user)->put(
            route('pharmacy.generics.update', $generic),
            ['name' => 'Amoxicillin', 'is_active' => true]
        );

        $this->assertDatabaseHas('generics', ['id' => $generic->id, 'name' => 'Amoxicillin']);
        $response->assertRedirect();
    }

    public function test_generic_can_be_deleted(): void
    {
        $generic = Generic::create(['name' => 'Test Generic']);

        $response = $this->actingAs($this->user)->delete(
            route('pharmacy.generics.destroy', $generic)
        );

        $this->assertDatabaseMissing('generics', ['id' => $generic->id]);
        $response->assertRedirect();
    }

    // ─── Medicine Units ────────────────────────────────────────────

    public function test_units_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.units.index'));

        $response->assertOk();
    }

    public function test_unit_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.units.store'),
            ['name' => 'Tablet', 'abbreviation' => 'Tab']
        );

        $this->assertDatabaseHas('medicine_units', ['name' => 'Tablet']);
        $response->assertRedirect();
    }

    public function test_unit_can_be_updated(): void
    {
        $unit = MedicineUnit::create(['name' => 'Capsule', 'abbreviation' => 'Cap']);

        $response = $this->actingAs($this->user)->put(
            route('pharmacy.units.update', $unit),
            ['name' => 'Capsules', 'abbreviation' => 'Caps']
        );

        $this->assertDatabaseHas('medicine_units', ['id' => $unit->id, 'name' => 'Capsules']);
        $response->assertRedirect();
    }

    public function test_unit_can_be_deleted(): void
    {
        $unit = MedicineUnit::create(['name' => 'ML', 'abbreviation' => 'mL']);

        $response = $this->actingAs($this->user)->delete(
            route('pharmacy.units.destroy', $unit)
        );

        $this->assertDatabaseMissing('medicine_units', ['id' => $unit->id]);
        $response->assertRedirect();
    }
}
