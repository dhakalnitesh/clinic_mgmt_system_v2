<?php

namespace Tests\Feature;

use App\Models\Consultation\Consultation;
use App\Models\Doctor\Doctor;
use App\Models\Laboratory\LabOrder;
use App\Models\Laboratory\LabOrderItem;
use App\Models\Laboratory\LabResult;
use App\Models\Laboratory\LabTest;
use App\Models\Laboratory\LabTestCategory;
use App\Models\Laboratory\LabTestParameter;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Patient $patient;
    private Doctor $doctor;
    private LabTestCategory $category;
    private LabTest $labTest;
    private LabTestParameter $parameter;

    protected function setUp(): void
    {
        parent::setUp();

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        foreach (['view dashboard', 'view patients', 'view appointments', 'view visits', 'view consultations'] as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm]);
        }
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        $this->patient = Patient::create([
            'name'   => 'Ram Prasad Sharma',
            'phone'  => '9800000000',
            'gender' => 'male',
            'age'    => 35,
        ]);

        $this->doctor = Doctor::create([
            'name'       => 'Dr. Hari Adhikari',
            'nmc_number' => 'NMC-12345',
            'phone'      => '9800000001',
        ]);

        $this->category = LabTestCategory::create([
            'name' => 'Hematology',
            'code' => 'HEM',
        ]);

        $this->labTest = LabTest::create([
            'lab_test_category_id' => $this->category->id,
            'name'  => 'Complete Blood Count',
            'code'  => 'CBC',
            'price' => 500,
        ]);

        $this->parameter = LabTestParameter::create([
            'lab_test_id'    => $this->labTest->id,
            'name'           => 'Hemoglobin',
            'unit'           => 'g/dL',
            'reference_range' => '13.5 - 17.5',
            'display_order'  => 1,
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    private function createFullLabOrder(string $orderNumber, string $status = 'completed'): LabOrder
    {
        $consultation = Consultation::create([
            'patient_id' => $this->patient->id,
            'doctor_id'  => $this->doctor->id,
        ]);

        $labOrder = LabOrder::create([
            'consultation_id' => $consultation->id,
            'patient_id'      => $this->patient->id,
            'doctor_id'       => $this->doctor->id,
            'order_number'    => $orderNumber,
            'status'          => $status,
            'created_by'      => $this->user->id,
        ]);

        $item = LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id'  => $this->labTest->id,
        ]);

        if ($status === 'completed') {
            LabResult::create([
                'lab_order_item_id'    => $item->id,
                'lab_test_parameter_id' => $this->parameter->id,
                'result_value'         => '14.2',
                'remarks'              => 'Within normal range',
            ]);
        }

        return $labOrder;
    }

    // ── Dashboard Tests ─────────────────────────────────────────────

    public function test_dashboard_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('laboratory.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Laboratory/Dashboard')
        );
    }

    public function test_dashboard_has_nepali_dates_in_weekly_data()
    {
        $response = $this->actingAs($this->user)
            ->get(route('laboratory.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('weeklyData.0.nepali_date', fn ($v) => $v !== null)
        );
    }

    public function test_dashboard_shows_correct_stats()
    {
        $this->createFullLabOrder('LAB-STATS-001', 'ordered');
        $this->createFullLabOrder('LAB-STATS-002', 'processing');
        $this->createFullLabOrder('LAB-STATS-003', 'completed');
        $this->createFullLabOrder('LAB-STATS-004', 'completed');

        $response = $this->actingAs($this->user)
            ->get(route('laboratory.dashboard'));

        $response->assertInertia(fn ($page) =>
            $page->where('stats.total', 4)
                 ->where('stats.ordered', 1)
                 ->where('stats.processing', 1)
                 ->where('stats.completed', 2)
        );
    }

    // ── Order Workflow Tests ────────────────────────────────────────

    public function test_order_workflow_collect_sample()
    {
        $labOrder = $this->createFullLabOrder('LAB-WF-001', 'ordered');

        $this->actingAs($this->user)->patch(
            route('laboratory.orders.collect-sample', $labOrder)
        );

        $this->assertDatabaseHas('lab_orders', [
            'id'     => $labOrder->id,
            'status' => 'sample_collected',
        ]);
    }

    public function test_order_workflow_full_cycle()
    {
        $labOrder = $this->createFullLabOrder('LAB-WF-002', 'ordered');

        $this->actingAs($this->user)
            ->patch(route('laboratory.orders.collect-sample', $labOrder));
        $this->actingAs($this->user)
            ->patch(route('laboratory.orders.start-processing', $labOrder));

        $this->actingAs($this->user)->post(
            route('laboratory.orders.results.store', $labOrder),
            [
                'results' => [
                    [
                        'lab_order_item_id'      => $labOrder->items->first()->id,
                        'lab_test_parameter_id'  => $this->parameter->id,
                        'result_value'           => '15.1',
                        'remarks'                => 'Normal',
                    ],
                ],
            ]
        );

        $this->assertDatabaseHas('lab_orders', [
            'id'     => $labOrder->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('lab_results', [
            'lab_order_item_id'    => $labOrder->items->first()->id,
            'lab_test_parameter_id' => $this->parameter->id,
            'result_value'         => '15.1',
        ]);
    }

    // ── Results Tests ───────────────────────────────────────────────

    public function test_results_index_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('laboratory.results.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Laboratory/Results/Index')
        );
    }

    public function test_results_index_shows_completed_orders()
    {
        $this->createFullLabOrder('LAB-RES-001', 'completed');
        $this->createFullLabOrder('LAB-RES-002', 'ordered');

        $response = $this->actingAs($this->user)
            ->get(route('laboratory.results.index'));

        $response->assertOk();

        $response->assertInertia(fn ($page) =>
            $page->has('orders.data', 1)
        );
    }

    // ── Nepali Date Tests ───────────────────────────────────────────

    public function test_lab_order_has_nepali_date()
    {
        $labOrder = $this->createFullLabOrder('LAB-BS-001', 'completed');

        $this->assertNotNull($labOrder->created_at_bs);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $labOrder->created_at_bs);
    }

    // ── Export Tests ────────────────────────────────────────────────

    public function test_results_print_loads()
    {
        $labOrder = $this->createFullLabOrder('LAB-PRINT-001', 'completed');

        $response = $this->actingAs($this->user)
            ->get(route('laboratory.orders.results.print', $labOrder));

        $response->assertOk();
    }

    public function test_results_csv_exports_with_data()
    {
        $labOrder = $this->createFullLabOrder('LAB-CSV-002', 'completed');

        $response = $this->actingAs($this->user)
            ->get(route('laboratory.orders.results.export.csv', $labOrder));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $response->assertHeader('Content-Disposition', 'attachment; filename="lab-results-' . $labOrder->order_number . '.csv"');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Hemoglobin', $content);
        $this->assertStringContainsString('14.2', $content);
        $this->assertStringContainsString('g/dL', $content);
        $this->assertStringContainsString($labOrder->order_number, $content);
    }

    public function test_results_pdf_exports_with_data()
    {
        $labOrder = $this->createFullLabOrder('LAB-PDF-002', 'completed');

        $response = $this->actingAs($this->user)
            ->get(route('laboratory.orders.results.export.pdf', $labOrder));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    // ── Test Parameters Tests ───────────────────────────────────────

    public function test_test_parameters_index_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('laboratory.test-parameters.index'));

        $response->assertOk();
    }

    public function test_test_parameters_create_and_store()
    {
        $response = $this->actingAs($this->user)->post(
            route('laboratory.test-parameters.store'),
            [
                'lab_test_id'     => $this->labTest->id,
                'name'            => 'WBC Count',
                'unit'            => 'x10^9/L',
                'reference_range' => '4.0 - 11.0',
                'display_order'   => 2,
                'is_active'       => true,
            ]
        );

        $this->assertDatabaseHas('lab_test_parameters', [
            'name' => 'WBC Count',
            'unit' => 'x10^9/L',
        ]);

        $response->assertRedirect(route('laboratory.test-parameters.index'));
    }

    public function test_test_parameters_edit_and_update()
    {
        $parameter = LabTestParameter::create([
            'lab_test_id' => $this->labTest->id,
            'name'        => 'WBC',
            'unit'        => 'x10^9/L',
        ]);

        $response = $this->actingAs($this->user)->put(
            route('laboratory.test-parameters.update', $parameter),
            [
                'lab_test_id'     => $this->labTest->id,
                'name'            => 'White Blood Cells',
                'unit'            => 'x10^9/L',
                'reference_range' => '4.0 - 11.0',
                'display_order'   => 2,
                'is_active'       => true,
            ]
        );

        $this->assertDatabaseHas('lab_test_parameters', [
            'id'   => $parameter->id,
            'name' => 'White Blood Cells',
        ]);

        $response->assertRedirect(route('laboratory.test-parameters.index'));
    }

    public function test_test_parameters_destroy()
    {
        $parameter = LabTestParameter::create([
            'lab_test_id' => $this->labTest->id,
            'name'        => 'Bacteria',
        ]);

        $response = $this->actingAs($this->user)->delete(
            route('laboratory.test-parameters.destroy', $parameter)
        );

        $this->assertDatabaseMissing('lab_test_parameters', [
            'id' => $parameter->id,
        ]);

        $response->assertRedirect();
    }

    // ── Orders Index Tests ──────────────────────────────────────────

    public function test_orders_index_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('laboratory.orders.index'));

        $response->assertOk();
    }

    public function test_orders_index_filters_by_status()
    {
        $this->createFullLabOrder('LAB-FILT-001', 'ordered');
        $this->createFullLabOrder('LAB-FILT-002', 'completed');

        $response = $this->actingAs($this->user)
            ->get(route('laboratory.orders.index', ['status' => 'ordered']));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('orders.data', 1)
        );
    }

    public function test_orders_show_loads()
    {
        $labOrder = $this->createFullLabOrder('LAB-SHOW-001', 'ordered');

        $response = $this->actingAs($this->user)
            ->get(route('laboratory.orders.show', $labOrder));

        $response->assertOk();
    }
}
