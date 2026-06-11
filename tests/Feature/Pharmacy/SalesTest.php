<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\Sales;
use App\Models\Pharmacy\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Medicine $medicine;
    private StockBatch $batch;

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
            'name'                 => 'Sale Medicine',
            'form'                 => 'tablet',
            'purchase_price'       => 10.00,
            'sale_price'           => 25.00,
            'reorder_level'        => 10,
            'reorder_quantity'     => 100,
            'is_prescription_required' => false,
        ]);

        $this->batch = StockBatch::create([
            'medicine_id' => $this->medicine->id,
            'batch_number' => 'SALE-BATCH-001',
            'expiry_date' => now()->addYears(2)->toDateString(),
            'quantity_received' => 200,
            'quantity_available' => 200,
            'purchase_price' => 10.00,
            'sale_price' => 25.00,
        ]);
    }

    public function test_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.sales.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Sales/Index'));
    }

    public function test_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.sales.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Sales/Create'));
    }

    public function test_sale_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.sales.store'),
            [
                'sale_date' => now()->toDateString(),
                'payment_mode' => 'cash',
                'paid_amount' => 50.00,
                'items' => [
                    [
                        'medicine_id' => $this->medicine->id,
                        'batch_id' => $this->batch->id,
                        'quantity' => 2,
                        'unit_price' => 25.00,
                    ],
                ],
            ]
        );

        $this->assertDatabaseHas('sales', [
            'cashier_id' => $this->user->id,
            'payment_mode' => 'cash',
        ]);

        $response->assertRedirect();
    }

    public function test_sale_show_page_loads(): void
    {
        $sale = Sales::create([
            'invoice_number' => 'INV-TEST-001',
            'sale_date' => now()->toDateString(),
            'cashier_id' => $this->user->id,
            'subtotal' => 50.00,
            'total_amount' => 50.00,
            'paid_amount' => 50.00,
            'payment_mode' => 'cash',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.sales.show', $sale));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Sales/Show'));
    }

    public function test_sale_requires_items(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.sales.store'),
            [
                'sale_date' => now()->toDateString(),
                'payment_mode' => 'cash',
                'paid_amount' => 0,
                'items' => [],
            ]
        );

        $response->assertSessionHasErrors('items');
    }

    public function test_index_filters_by_status(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.sales.index', ['status' => 'completed']));

        $response->assertOk();
    }

    public function test_index_returns_summary(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.sales.index'));

        $response->assertInertia(fn ($page) =>
            $page->has('summary', fn ($s) =>
                $s->has('today_count')
                  ->has('today_amount')
                  ->has('total_count')
                  ->has('returns')
            )
        );
    }

    public function test_check_interactions_api(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.sales.check-interactions'),
            ['medicine_ids' => [$this->medicine->id]]
        );

        $response->assertOk();
        $response->assertJsonStructure([]);
    }

    public function test_suggest_batches_api(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.sales.suggest-batches'),
            [
                'medicine_id' => $this->medicine->id,
                'quantity' => 5,
            ]
        );

        $response->assertOk();
        $response->assertJsonStructure(['batches', 'sufficient']);
    }
}
