<?php

namespace Tests\Feature;

use App\Models\Laboratory\LabTest;
use App\Models\Laboratory\LabTestCategory;
use App\Models\Laboratory\LabTestParameter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        // Create needed permissions
        foreach (['view dashboard', 'view patients', 'view appointments', 'view visits', 'view consultations'] as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm]);
        }
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    }

    public function test_dashboard_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('laboratory.dashboard'));

        $response->assertOk();
    }

    public function test_orders_index_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('laboratory.orders.index'));

        $response->assertOk();
    }

    public function test_test_parameters_index_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('laboratory.test-parameters.index'));

        $response->assertOk();
    }

    public function test_test_parameters_create_and_store()
    {
        $category = LabTestCategory::create([
            'name' => 'Hematology',
            'code' => 'HEM',
        ]);

        $labTest = LabTest::create([
            'lab_test_category_id' => $category->id,
            'name' => 'CBC',
            'code' => 'CBC',
            'price' => 500,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('laboratory.test-parameters.store'),
            [
                'lab_test_id' => $labTest->id,
                'name' => 'Hemoglobin',
                'unit' => 'g/dL',
                'reference_range' => '13.5 - 17.5',
                'display_order' => 1,
                'is_active' => true,
            ]
        );

        $this->assertDatabaseHas('lab_test_parameters', [
            'name' => 'Hemoglobin',
            'unit' => 'g/dL',
        ]);

        $response->assertRedirect(route('laboratory.test-parameters.index'));
    }

    public function test_test_parameters_edit_and_update()
    {
        $category = LabTestCategory::create([
            'name' => 'Biochemistry',
            'code' => 'BIO',
        ]);

        $labTest = LabTest::create([
            'lab_test_category_id' => $category->id,
            'name' => 'LFT',
            'code' => 'LFT',
            'price' => 800,
        ]);

        $parameter = LabTestParameter::create([
            'lab_test_id' => $labTest->id,
            'name' => 'WBC',
            'unit' => 'x10^9/L',
        ]);

        $response = $this->actingAs($this->user)->put(
            route('laboratory.test-parameters.update', $parameter),
            [
                'lab_test_id' => $labTest->id,
                'name' => 'White Blood Cells',
                'unit' => 'x10^9/L',
                'reference_range' => '4.0 - 11.0',
                'display_order' => 2,
                'is_active' => true,
            ]
        );

        $this->assertDatabaseHas('lab_test_parameters', [
            'id' => $parameter->id,
            'name' => 'White Blood Cells',
        ]);

        $response->assertRedirect(route('laboratory.test-parameters.index'));
    }

    public function test_test_parameters_destroy()
    {
        $category = LabTestCategory::create([
            'name' => 'Microbiology',
            'code' => 'MICRO',
        ]);

        $labTest = LabTest::create([
            'lab_test_category_id' => $category->id,
            'name' => 'Urine Culture',
            'code' => 'UC',
            'price' => 300,
        ]);

        $parameter = LabTestParameter::create([
            'lab_test_id' => $labTest->id,
            'name' => 'Bacteria',
        ]);

        $response = $this->actingAs($this->user)->delete(
            route('laboratory.test-parameters.destroy', $parameter)
        );

        $this->assertDatabaseMissing('lab_test_parameters', [
            'id' => $parameter->id,
        ]);

        $response->assertRedirect();
    }
}
