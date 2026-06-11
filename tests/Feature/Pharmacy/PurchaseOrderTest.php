<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $category = MedicineCategory::create(['name' => 'Antibiotics', 'is_active' => true]);
        $generic  = Generic::create(['name' => 'Amoxicillin', 'is_active' => true]);
        $unit     = MedicineUnit::create(['name' => 'Capsule', 'abbreviation' => 'Cap']);

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'credit_days' => 30,
            'is_active' => true,
        ]);

        $this->medicine = Medicine::create([
            'medicine_category_id' => $category->id,
            'generic_id'           => $generic->id,
            'medicine_unit_id'     => $unit->id,
            'name'                 => 'Test Medicine',
            'form'                 => 'capsule',
            'purchase_price'       => 50.00,
            'sale_price'           => 75.00,
            'reorder_level'        => 10,
            'reorder_quantity'     => 100,
        ]);
    }

    public function test_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.purchase-orders.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/PurchaseOrders/Index'));
    }

    public function test_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.purchase-orders.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/PurchaseOrders/Create'));
    }

    public function test_purchase_order_can_be_stored(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.purchase-orders.store'),
            [
                'supplier_id' => $this->supplier->id,
                'order_date'  => now()->toDateString(),
                'items' => [
                    [
                        'medicine_id'      => $this->medicine->id,
                        'quantity_ordered' => 50,
                        'unit_price'       => 50.00,
                        'discount_percent' => 0,
                        'tax_percent'      => 0,
                    ],
                ],
            ]
        );

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'medicine_id' => $this->medicine->id,
            'quantity_ordered' => 50,
        ]);

        $response->assertRedirect();
    }

    public function test_purchase_order_requires_items(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.purchase-orders.store'),
            [
                'supplier_id' => $this->supplier->id,
                'order_date'  => now()->toDateString(),
                'items' => [],
            ]
        );

        $response->assertSessionHasErrors('items');
    }

    public function test_purchase_order_show_page_loads(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'ordered_by'  => $this->user->id,
            'po_number'   => 'PO-2024-00001',
            'order_date'  => now()->toDateString(),
            'status'      => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.purchase-orders.show', $po));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/PurchaseOrders/Show'));
    }

    public function test_purchase_order_can_be_sent(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'ordered_by'  => $this->user->id,
            'po_number'   => 'PO-2024-00002',
            'order_date'  => now()->toDateString(),
            'status'      => 'draft',
        ]);

        $this->actingAs($this->user)->patch(
            route('pharmacy.purchase-orders.send', $po)
        );

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'sent',
        ]);
    }

    public function test_purchase_order_can_be_cancelled(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'ordered_by'  => $this->user->id,
            'po_number'   => 'PO-2024-00003',
            'order_date'  => now()->toDateString(),
            'status'      => 'draft',
        ]);

        $this->actingAs($this->user)->patch(
            route('pharmacy.purchase-orders.cancel', $po)
        );

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_index_filters_by_status(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.purchase-orders.index', ['status' => 'draft']));

        $response->assertOk();
    }
}
