<?php

namespace Tests\Feature\Pharmacy;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_pharmacy_dashboard_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Dashboard'));
    }

    public function test_pharmacy_dashboard_receives_kpis(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.dashboard'));

        $response->assertInertia(fn ($page) => $page->has('kpis'));
    }

    public function test_pharmacy_dashboard_receives_last_7_days(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.dashboard'));

        $response->assertInertia(fn ($page) => $page->has('last7_days'));
    }

    public function test_pharmacy_dashboard_receives_top_medicines(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.dashboard'));

        $response->assertInertia(fn ($page) => $page->has('top_medicines'));
    }

    public function test_pharmacy_dashboard_receives_near_expiry_items(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.dashboard'));

        $response->assertInertia(fn ($page) => $page->has('near_expiry_items'));
    }
}
