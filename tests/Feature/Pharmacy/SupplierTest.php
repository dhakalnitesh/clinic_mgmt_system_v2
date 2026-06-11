<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Pharmacy\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.suppliers.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Suppliers/Index'));
    }

    public function test_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.suppliers.create'));

        $response->assertOk();
    }

    public function test_supplier_can_be_stored(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.suppliers.store'),
            [
                'name'            => 'Nepal Drug House',
                'phone'           => '9812345678',
                'email'           => 'info@nepaldrug.com',
                'city'            => 'Kathmandu',
                'credit_days'     => 30,
                'credit_limit'    => 100000,
                'opening_balance' => 0,
                'is_active'       => true,
            ]
        );

        $this->assertDatabaseHas('suppliers', ['name' => 'Nepal Drug House']);
        $response->assertRedirect();
    }

    public function test_supplier_requires_name(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.suppliers.store'),
            ['name' => '']
        );

        $response->assertSessionHasErrors('name');
    }

    public function test_show_page_loads(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'credit_days' => 30,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.suppliers.show', $supplier));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Suppliers/Show'));
    }

    public function test_edit_page_loads(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'credit_days' => 30,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.suppliers.edit', $supplier));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Suppliers/Edit'));
    }

    public function test_supplier_can_be_updated(): void
    {
        $supplier = Supplier::create([
            'name' => 'Old Name',
            'credit_days' => 30,
        ]);

        $response = $this->actingAs($this->user)->put(
            route('pharmacy.suppliers.update', $supplier),
            [
                'name'            => 'Updated Supplier',
                'phone'           => '9800000000',
                'credit_days'     => 45,
                'credit_limit'    => 100000,
                'opening_balance' => 0,
                'is_active'       => true,
            ]
        );

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Supplier',
            'credit_days' => 45,
        ]);
        $response->assertRedirect();
    }

    public function test_supplier_can_be_toggled_active(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'is_active' => true,
            'credit_days' => 30,
        ]);

        $this->actingAs($this->user)->patch(
            route('pharmacy.suppliers.toggle-active', $supplier)
        );

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'is_active' => false]);
    }

    public function test_supplier_can_be_deleted(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'credit_days' => 30,
        ]);

        $response = $this->actingAs($this->user)->delete(
            route('pharmacy.suppliers.destroy', $supplier)
        );

        $this->assertSoftDeleted($supplier);
        $response->assertRedirect(route('pharmacy.suppliers.index'));
    }

    public function test_search_api_returns_json(): void
    {
        Supplier::create([
            'name' => 'ABC Pharma',
            'phone' => '9812345678',
            'credit_days' => 30,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.suppliers.search', ['q' => 'ABC']));

        $response->assertOk();
        $response->assertJsonStructure([['id', 'name']]);
    }
}
