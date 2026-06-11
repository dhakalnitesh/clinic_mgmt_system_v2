<?php

namespace Tests\Feature;

use App\Models\Consultation\Consultation;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function createPatient(): Patient
    {
        return Patient::create([
            'name' => 'Test Patient',
            'phone' => '9841234567',
            'gender' => 'Male',
            'age' => 30,
        ]);
    }

    private function createDoctor(): Doctor
    {
        return Doctor::create([
            'name' => 'Dr. Test',
            'nmc_number' => 'NMC-' . rand(10000, 99999),
            'specialization' => 'General',
            'phone' => '9840000000',
            'consultation_fee' => 500,
        ]);
    }

    private function createVisit($patient, $doctor): Visit
    {
        return Visit::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'visit_type' => 'walk_in',
            'status' => 'waiting',
        ]);
    }

    public function test_index_page_renders()
    {
        $response = $this->actingAs($this->user)->get(route('consultations.index'));
        $response->assertOk();
    }

    public function test_store_creates_consultation_and_vitals()
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $visit = $this->createVisit($patient, $doctor);

        $response = $this->actingAs($this->user)->post(route('consultations.store'), [
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'chief_complaint' => 'Headache',
            'diagnosis' => 'Migraine',
            'blood_pressure' => '120/80',
        ]);

        $this->assertDatabaseHas('consultations', [
            'patient_id' => $patient->id,
            'chief_complaint' => 'Headache',
        ]);
        $this->assertDatabaseHas('vitals', [
            'patient_id' => $patient->id,
            'blood_pressure' => '120/80',
        ]);

        $consultation = Consultation::first();
        $response->assertRedirect(route('consultations.show', $consultation->id));
    }

    public function test_store_creates_prescription_with_medicines()
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $visit = $this->createVisit($patient, $doctor);

        $this->actingAs($this->user)->post(route('consultations.store'), [
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'chief_complaint' => 'Cough',
            'medicines' => [
                [
                    'medicine_name' => 'Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => 'thrice_daily',
                    'duration' => '5 days',
                    'quantity' => '15 tablets',
                ],
            ],
        ]);

        $consultation = Consultation::first();
        $this->assertDatabaseHas('prescriptions', ['consultation_id' => $consultation->id]);
        $this->assertDatabaseHas('prescription_items', [
            'medicine_name' => 'Paracetamol',
            'dosage' => '500mg',
        ]);
    }

    public function test_store_marks_visit_as_completed()
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $visit = $this->createVisit($patient, $doctor);
        $visit->update(['status' => 'in_consultation']);

        $this->actingAs($this->user)->post(route('consultations.store'), [
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'chief_complaint' => 'Fever',
        ]);

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'status' => 'completed',
        ]);
    }

    public function test_show_page_displays_consultation()
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $visit = $this->createVisit($patient, $doctor);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'chief_complaint' => 'Fever',
            'consultation_status' => 'completed',
            'consulted_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('consultations.show', $consultation));

        $response->assertOk();
    }
}
