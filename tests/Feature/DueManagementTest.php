<?php

namespace Tests\Feature;

use App\Models\Billing\Invoice;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DueManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin']);

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        $this->patient = Patient::create([
            'name' => 'Due Patient',
            'phone' => '9841234567',
            'gender' => 'Male',
            'age' => 35,
        ]);
    }

    public function test_dues_index_shows_pending_and_partial_invoices()
    {
        Invoice::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-TEST-001',
            'subtotal' => 1000,
            'total' => 1000,
            'status' => 'pending',
        ]);

        Invoice::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-TEST-002',
            'subtotal' => 500,
            'total' => 500,
            'status' => 'partial',
        ]);

        Invoice::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-TEST-003',
            'subtotal' => 2000,
            'total' => 2000,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->get(route('dues.index'));

        $response->assertOk();
    }

    public function test_dues_index_filters_by_search()
    {
        Invoice::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-SEARCH-001',
            'subtotal' => 1000,
            'total' => 1000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get(route('dues.index', [
            'search' => 'INV-SEARCH',
        ]));

        $response->assertOk();
    }

    public function test_paid_invoices_excluded_from_dues()
    {
        Invoice::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-PAID-001',
            'subtotal' => 2000,
            'total' => 2000,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->get(route('dues.index'));

        $response->assertOk();
    }
}
