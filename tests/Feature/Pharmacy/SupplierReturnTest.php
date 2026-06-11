<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\StockBatch;
use App\Models\Pharmacy\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierReturnTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private StockBatch $batch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'credit_days' => 30,
            'is_active' => true,
        ]);

        $category = MedicineCategory::create(['name' => 'Test Cat', 'is_active' => true]);
        $generic  = Generic::create(['name' => 'Test Generic', 'is_active' => true]);
        $unit     = MedicineUnit::create(['name' => 'Tablet', 'abbreviation' => 'Tab']);

        $medicine = Medicine::create([
            'medicine_category_id' => $category->id,
            'generic_id'           => $generic->id,
            'medicine_unit_id'     => $unit->id,
            'name'                 => 'Return Medicine',
            'form'                 => 'tablet',
            'purchase_price'       => 10.00,
            'sale_price'           => 20.00,
            'reorder_level'        => 10,
            'reorder_quantity'     => 100,
        ]);

        $this->batch = StockBatch::create([
            'medicine_id' => $medicine->id,
            'supplier_id' => $this->supplier->id,
            'batch_number' => 'SR-BATCH-001',
            'expiry_date' => now()->addYears(2)->toDateString(),
            'quantity_received' => 100,
            'quantity_available' => 100,
            'purchase_price' => 10.00,
            'sale_price' => 20.00,
        ]);
    }

    public function test_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.supplier-returns.index'));

        $response->assertOk();
    }

    public function test_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.supplier-returns.create'));

        $response->assertOk();
    }

    public function test_return_can_be_stored(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.supplier-returns.store'),
            [
                'supplier_id' => $this->supplier->id,
                'return_date' => now()->toDateString(),
                'items' => [
                    [
                        'stock_batch_id' => $this->batch->id,
                        'quantity' => 5,
                        'reason' => 'Expired stock',
                    ],
                ],
            ]
        );

        $this->assertDatabaseHas('supplier_returns', [
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response->assertRedirect();
    }

    public function test_show_page_loads(): void
    {
        $supplierReturn = \App\Models\Pharmacy\SupplierReturn::create([
            'supplier_id'  => $this->supplier->id,
            'returned_by'  => $this->user->id,
            'return_number'=> 'SR-TEST-001',
            'return_date'  => now()->toDateString(),
            'status'       => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.supplier-returns.show', $supplierReturn));

        $response->assertOk();
    }
}
