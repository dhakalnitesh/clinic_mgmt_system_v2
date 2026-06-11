<?php

namespace Tests\Feature\Pharmacy;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::factory()->create();
    }

    public function test_report_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.reports.index'));

        $response->assertOk();
    }

    public function test_sales_report_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.reports.sales'));

        $response->assertOk();
    }

    public function test_stock_report_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.reports.stock'));

        $response->assertOk();
    }

    public function test_expiry_report_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.reports.expiry'));

        $response->assertOk();
    }

    public function test_purchases_report_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.reports.purchases'));

        $response->assertOk();
    }

    public function test_slow_moving_report_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.reports.slow-moving'));

        $response->assertOk();
    }
}
