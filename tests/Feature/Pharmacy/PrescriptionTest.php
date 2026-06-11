<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Patient $patient;
    private Doctor $doctor;
    private Medicine $medicine;
    private Generic $generic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->patient = Patient::create(['name' => 'Test Patient', 'phone' => '9800000000', 'gender' => 'male', 'age' => 30]);
        $this->doctor = Doctor::create(['name' => 'Test Doctor', 'nmc_number' => 'NMC-12345', 'specialization' => 'General', 'phone' => '9800000001']);

        $category = MedicineCategory::create(['name' => 'Test Cat', 'is_active' => true]);
        $this->generic = Generic::create(['name' => 'Test Generic', 'is_active' => true]);
        $unit = MedicineUnit::create(['name' => 'Tablet', 'abbreviation' => 'Tab']);

        $this->medicine = Medicine::create([
            'medicine_category_id' => $category->id,
            'generic_id'           => $this->generic->id,
            'medicine_unit_id'     => $unit->id,
            'name'                 => 'Rx Medicine',
            'form'                 => 'tablet',
            'purchase_price'       => 10.00,
            'sale_price'           => 25.00,
            'reorder_level'        => 10,
            'reorder_quantity'     => 100,
        ]);
    }

    public function test_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.prescriptions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Prescriptions/Index'));
    }

    public function test_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.prescriptions.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Prescriptions/Create'));
    }

    public function test_prescription_can_be_stored(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.prescriptions.store'),
            [
                'patient_id'        => $this->patient->id,
                'doctor_id'         => $this->doctor->id,
                'prescription_date' => now()->toDateString(),
                'items' => [
                    [
                        'medicine_id' => $this->medicine->id,
                        'generic_id' => $this->generic->id,
                        'dosage_instruction' => '1+1+1',
                        'quantity_prescribed' => 30,
                    ],
                ],
            ]
        );

        $this->assertDatabaseHas('prescriptions', [
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('prescription_items', [
            'medicine_id' => $this->medicine->id,
            'quantity_prescribed' => 30,
        ]);

        $response->assertRedirect();
    }

    public function test_prescription_show_page_loads(): void
    {
        $prescription = Prescription::create([
            'prescription_number' => 'RX-TEST-001',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.prescriptions.show', $prescription));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Pharmacy/Prescriptions/Show'));
    }

    public function test_index_filters_by_status(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.prescriptions.index', ['status' => 'pending']));

        $response->assertOk();
    }

    public function test_index_returns_summary(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.prescriptions.index'));

        $response->assertInertia(fn ($page) =>
            $page->has('summary', fn ($s) =>
                $s->has('total')
                  ->has('pending')
                  ->has('partial')
                  ->has('dispensed')
            )
        );
    }

    public function test_prescription_requires_items(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.prescriptions.store'),
            [
                'patient_id'        => $this->patient->id,
                'doctor_id'         => $this->doctor->id,
                'prescription_date' => now()->toDateString(),
                'items' => [],
            ]
        );

        $response->assertSessionHasErrors('items');
    }

    public function test_prescription_requires_patient_and_doctor(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.prescriptions.store'),
            [
                'prescription_date' => now()->toDateString(),
                'items' => [
                    [
                        'medicine_id' => $this->medicine->id,
                        'quantity_prescribed' => 10,
                    ],
                ],
            ]
        );

        $response->assertSessionHasErrors(['patient_id', 'doctor_id']);
    }
}
