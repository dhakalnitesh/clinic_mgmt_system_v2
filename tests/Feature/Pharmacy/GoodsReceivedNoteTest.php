<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\GoodsReceivedNote;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceivedNoteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Medicine $medicine;
    private PurchaseOrder $po;

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

        $this->po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'ordered_by'  => $this->user->id,
            'po_number'   => 'PO-TEST-001',
            'order_date'  => now()->toDateString(),
            'status'      => 'sent',
        ]);
    }

    public function test_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.grn.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/GRN/Index'));
    }

    public function test_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.grn.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/GRN/Create'));
    }

    public function test_grn_can_be_stored(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.grn.store'),
            [
                'supplier_id' => $this->supplier->id,
                'purchase_order_id' => $this->po->id,
                'received_date' => now()->toDateString(),
                'items' => [
                    [
                        'medicine_id'       => $this->medicine->id,
                        'batch_number'      => 'BATCH-001',
                        'expiry_date'       => now()->addYears(2)->toDateString(),
                        'quantity_received' => 100,
                        'free_quantity'     => 0,
                        'unit_price'        => 50.00,
                        'sale_price'        => 75.00,
                        'discount_percent'  => 0,
                        'tax_percent'       => 0,
                    ],
                ],
            ]
        );

        $this->assertDatabaseHas('goods_received_notes', [
            'supplier_id' => $this->supplier->id,
            'status' => 'pending',
        ]);

        $response->assertRedirect();
    }

    public function test_grn_show_page_loads(): void
    {
        $grn = GoodsReceivedNote::create([
            'supplier_id' => $this->supplier->id,
            'received_by' => $this->user->id,
            'grn_number'  => 'GRN-TEST-001',
            'received_date' => now()->toDateString(),
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.grn.show', $grn));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/GRN/Show'));
    }

    public function test_grn_can_be_verified(): void
    {
        $grn = GoodsReceivedNote::create([
            'supplier_id' => $this->supplier->id,
            'received_by' => $this->user->id,
            'grn_number'  => 'GRN-TEST-002',
            'received_date' => now()->toDateString(),
            'status'      => 'pending',
        ]);

        $this->actingAs($this->user)->patch(
            route('pharmacy.grn.verify', $grn)
        );

        $this->assertDatabaseHas('goods_received_notes', [
            'id' => $grn->id,
            'status' => 'verified',
        ]);
    }

    public function test_grn_can_be_posted(): void
    {
        $grn = GoodsReceivedNote::create([
            'supplier_id' => $this->supplier->id,
            'received_by' => $this->user->id,
            'grn_number'  => 'GRN-TEST-003',
            'received_date' => now()->toDateString(),
            'status'      => 'verified',
        ]);

        $this->actingAs($this->user)->patch(
            route('pharmacy.grn.post', $grn)
        );

        $this->assertDatabaseHas('goods_received_notes', [
            'id' => $grn->id,
            'status' => 'posted',
        ]);
    }

    public function test_grn_can_be_deleted(): void
    {
        $grn = GoodsReceivedNote::create([
            'supplier_id' => $this->supplier->id,
            'received_by' => $this->user->id,
            'grn_number'  => 'GRN-TEST-004',
            'received_date' => now()->toDateString(),
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->user)->delete(
            route('pharmacy.grn.destroy', $grn)
        );

        $this->assertDatabaseMissing('goods_received_notes', ['id' => $grn->id]);
        $response->assertRedirect();
    }

    public function test_index_filters_by_status(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.grn.index', ['status' => 'pending']));

        $response->assertOk();
    }
}
