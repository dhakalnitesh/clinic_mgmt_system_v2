<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\StockBatch;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $category = MedicineCategory::create(['name' => 'Test Cat', 'is_active' => true]);
        $generic  = Generic::create(['name' => 'Test Generic', 'is_active' => true]);
        $unit     = MedicineUnit::create(['name' => 'Tablet', 'abbreviation' => 'Tab']);

        $this->medicine = Medicine::create([
            'medicine_category_id' => $category->id,
            'generic_id'           => $generic->id,
            'medicine_unit_id'     => $unit->id,
            'name'                 => 'Test Medicine',
            'form'                 => 'tablet',
            'purchase_price'       => 10.00,
            'sale_price'           => 20.00,
            'reorder_level'        => 10,
            'reorder_quantity'     => 100,
        ]);
    }

    public function test_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.inventory.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Inventory/Index'));
    }

    public function test_index_returns_summary(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.inventory.index'));

        $response->assertInertia(fn ($page) =>
            $page->has('summary', fn ($s) =>
                $s->has('total_medicines')
                  ->has('total_stock_value')
                  ->has('low_stock_count')
                  ->has('near_expiry_count')
                  ->has('expired_count')
                  ->has('out_of_stock')
            )
        );
    }

    public function test_index_filters_by_expiry(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.inventory.index', ['expiry' => 'near']));

        $response->assertOk();
    }

    public function test_batch_detail_page_loads(): void
    {
        $batch = StockBatch::create([
            'medicine_id' => $this->medicine->id,
            'batch_number' => 'BATCH-TEST-001',
            'expiry_date' => now()->addYears(1)->toDateString(),
            'quantity_received' => 100,
            'quantity_available' => 100,
            'purchase_price' => 10.00,
            'sale_price' => 20.00,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.inventory.batch', $batch));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Inventory/BatchDetail'));
    }

    public function test_stock_adjustment_can_be_recorded(): void
    {
        $batch = StockBatch::create([
            'medicine_id' => $this->medicine->id,
            'batch_number' => 'BATCH-ADJ-001',
            'expiry_date' => now()->addYears(1)->toDateString(),
            'quantity_received' => 100,
            'quantity_available' => 100,
            'purchase_price' => 10.00,
            'sale_price' => 20.00,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('pharmacy.inventory.adjust'),
            [
                'medicine_id' => $this->medicine->id,
                'stock_batch_id' => $batch->id,
                'adjustment_type' => 'deduction',
                'quantity' => 10,
                'reason' => 'Damaged during handling',
            ]
        );

        $this->assertDatabaseHas('stock_adjustments', [
            'medicine_id' => $this->medicine->id,
            'adjustment_type' => 'deduction',
            'quantity' => -10,
        ]);

        $this->assertDatabaseHas('stock_batches', [
            'id' => $batch->id,
            'quantity_available' => 90,
        ]);

        $response->assertRedirect();
    }

    public function test_stock_adjustment_requires_reason(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.inventory.adjust'),
            [
                'medicine_id' => $this->medicine->id,
                'adjustment_type' => 'deduction',
                'quantity' => 10,
                'reason' => '',
            ]
        );

        $response->assertSessionHasErrors('reason');
    }
}
