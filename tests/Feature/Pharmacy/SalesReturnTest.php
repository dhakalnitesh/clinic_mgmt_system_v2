<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\Sales;
use App\Models\Pharmacy\SalesItem;
use App\Models\Pharmacy\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReturnTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Sales $sale;
    private SalesItem $saleItem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $category = MedicineCategory::create(['name' => 'Test Cat', 'is_active' => true]);
        $generic  = Generic::create(['name' => 'Test Generic', 'is_active' => true]);
        $unit     = MedicineUnit::create(['name' => 'Tablet', 'abbreviation' => 'Tab']);

        $medicine = Medicine::create([
            'medicine_category_id' => $category->id,
            'generic_id'           => $generic->id,
            'medicine_unit_id'     => $unit->id,
            'name'                 => 'Test Medicine',
            'form'                 => 'tablet',
            'purchase_price'       => 10.00,
            'sale_price'           => 25.00,
            'reorder_level'        => 10,
            'reorder_quantity'     => 100,
        ]);

        $batch = StockBatch::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'RET-BATCH-001',
            'expiry_date' => now()->addYears(2)->toDateString(),
            'quantity_received' => 100,
            'quantity_available' => 80,
            'purchase_price' => 10.00,
            'sale_price' => 25.00,
        ]);

        $this->sale = Sales::create([
            'invoice_number' => 'INV-RET-001',
            'sale_date' => now()->toDateString(),
            'cashier_id' => $this->user->id,
            'subtotal' => 50.00,
            'total_amount' => 50.00,
            'paid_amount' => 50.00,
            'payment_mode' => 'cash',
            'status' => 'completed',
        ]);

        $this->saleItem = SalesItem::create([
            'sale_id' => $this->sale->id,
            'medicine_id' => $medicine->id,
            'stock_batch_id' => $batch->id,
            'quantity' => 2,
            'unit_price' => 25.00,
            'subtotal' => 50.00,
        ]);
    }

    public function test_return_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.sales.return.create', $this->sale));

        $response->assertOk();
    }

    public function test_return_can_be_stored(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.sales-returns.store'),
            [
                'sale_id' => $this->sale->id,
                'return_date' => now()->toDateString(),
                'refund_mode' => 'cash',
                'items' => [
                    [
                        'sale_item_id' => $this->saleItem->id,
                        'quantity' => 1,
                        'reason' => 'Damaged product',
                    ],
                ],
            ]
        );

        $this->assertDatabaseHas('sales_returns', [
            'sale_id' => $this->sale->id,
            'status' => 'completed',
        ]);

        $response->assertRedirect();
    }
}
