<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient\Patient;
use App\Models\Doctor\Doctor;
use App\Models\Visit\Visit;
use App\Models\Consultation\Consultation;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\Supplier;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\PurchaseOrderItem;
use App\Models\Pharmacy\GoodsReceivedNote;
use App\Models\Pharmacy\GrnItem;
use App\Models\Pharmacy\StockBatch;
use App\Models\Pharmacy\StockAdjustment;
use App\Models\Pharmacy\Sales;
use App\Models\Pharmacy\SalesItem;
use App\Models\Pharmacy\SalesReturn;
use App\Models\Pharmacy\SalesReturnItem;
use App\Models\Pharmacy\SupplierReturn;
use App\Models\Pharmacy\SupplierReturnItem;
use App\Models\Pharmacy\DrugInteraction;
use App\Models\Pharmacy\Prescription;
use App\Models\Pharmacy\PrescriptionItem;
use App\Models\Laboratory\LabTestCategory;
use App\Models\Laboratory\LabTest;
use App\Models\Laboratory\LabTestParameter;
use App\Models\Laboratory\LabOrder;
use App\Models\Laboratory\LabOrderItem;
use App\Models\Laboratory\LabResult;
use App\Models\Billing\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeepTestSuitePharmacyLabTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@gmail.com')->first();
        $this->doctor = User::where('email', 'doctor@gmail.com')->first();
    }

    // ========================================================================
    // PHARMACY - SETTINGS (Categories, Generics, Units) - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_pharmacy_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_medicine_category_create()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/categories', [
            'name' => 'Antibiotic',
            'description' => 'Antibiotic medicines',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('medicine_categories', ['name' => 'Antibiotic']);
    }

    /** @test */
    public function test_medicine_category_create_duplicate_name_fails()
    {
        MedicineCategory::factory()->create(['name' => 'Antibiotic']);
        $response = $this->actingAs($this->admin)->post('/pharmacy/categories', [
            'name' => 'Antibiotic',
        ]);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_medicine_category_create_with_xss()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/categories', [
            'name' => '<script>alert("xss")</script>',
        ]);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_medicine_category_update()
    {
        $category = MedicineCategory::factory()->create(['name' => 'Old Cat']);
        $response = $this->actingAs($this->admin)->put("/pharmacy/categories/{$category->id}", [
            'name' => 'Updated Cat',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('medicine_categories', ['id' => $category->id, 'name' => 'Updated Cat']);
    }

    /** @test */
    public function test_medicine_category_delete()
    {
        $category = MedicineCategory::factory()->create(['name' => 'Delete Cat']);
        $response = $this->actingAs($this->admin)->delete("/pharmacy/categories/{$category->id}");
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('medicine_categories', ['id' => $category->id]);
    }

    /** @test */
    public function test_generic_create()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/generics', [
            'name' => 'Paracetamol',
            'pharmacological_class' => 'Analgesic',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('generics', ['name' => 'Paracetamol']);
    }

    /** @test */
    public function test_generic_create_duplicate_name_fails()
    {
        Generic::factory()->create(['name' => 'Paracetamol']);
        $response = $this->actingAs($this->admin)->post('/pharmacy/generics', [
            'name' => 'Paracetamol',
        ]);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_generic_update()
    {
        $generic = Generic::factory()->create(['name' => 'Old Generic']);
        $response = $this->actingAs($this->admin)->put("/pharmacy/generics/{$generic->id}", [
            'name' => 'Updated Generic',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('generics', ['id' => $generic->id, 'name' => 'Updated Generic']);
    }

    /** @test */
    public function test_generic_delete()
    {
        $generic = Generic::factory()->create(['name' => 'Delete Generic']);
        $response = $this->actingAs($this->admin)->delete("/pharmacy/generics/{$generic->id}");
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('generics', ['id' => $generic->id]);
    }

    /** @test */
    public function test_medicine_unit_create()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/units', [
            'name' => 'Tablet',
            'abbreviation' => 'tab',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('medicine_units', ['name' => 'Tablet']);
    }

    /** @test */
    public function test_medicine_unit_create_duplicate_name_fails()
    {
        MedicineUnit::factory()->create(['name' => 'Tablet']);
        $response = $this->actingAs($this->admin)->post('/pharmacy/units', [
            'name' => 'Tablet',
            'abbreviation' => 'tab',
        ]);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_medicine_unit_update()
    {
        $unit = MedicineUnit::factory()->create(['name' => 'Old Unit']);
        $response = $this->actingAs($this->admin)->put("/pharmacy/units/{$unit->id}", [
            'name' => 'Updated Unit',
            'abbreviation' => 'uu',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('medicine_units', ['id' => $unit->id, 'name' => 'Updated Unit']);
    }

    /** @test */
    public function test_medicine_unit_delete()
    {
        $unit = MedicineUnit::factory()->create(['name' => 'Delete Unit']);
        $response = $this->actingAs($this->admin)->delete("/pharmacy/units/{$unit->id}");
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('medicine_units', ['id' => $unit->id]);
    }

    // ========================================================================
    // PHARMACY - MEDICINES - EXHAUSTIVE TESTS
    // ========================================================================

    private function createMedicineDeps(): array
    {
        $category = MedicineCategory::factory()->create();
        $generic = Generic::factory()->create();
        $unit = MedicineUnit::factory()->create();
        return [$category, $generic, $unit];
    }

    /** @test */
    public function test_medicine_index()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/medicines');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_medicine_create()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'medicine_category_id' => $cat->id,
            'generic_id' => $gen->id,
            'medicine_unit_id' => $unit->id,
            'name' => 'Paracetamol 500mg',
            'form' => 'tablet',
            'purchase_price' => 50,
            'sale_price' => 100,
            'tax_percent' => 0,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('medicines', ['name' => 'Paracetamol 500mg']);
    }

    /** @test */
    public function test_medicine_create_with_all_fields()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'medicine_category_id' => $cat->id,
            'generic_id' => $gen->id,
            'medicine_unit_id' => $unit->id,
            'name' => 'Amoxicillin 250mg',
            'strength' => '250mg',
            'form' => 'capsule',
            'manufacturer' => 'Test Pharma',
            'barcode' => 'BARCODE123',
            'hsn_code' => '3003',
            'purchase_price' => 80,
            'sale_price' => 150,
            'mrp' => 160,
            'tax_percent' => 5,
            'reorder_level' => 20,
            'reorder_quantity' => 200,
            'shelf_location' => 'A-1',
            'is_prescription_required' => true,
            'is_controlled' => false,
            'is_active' => true,
            'notes' => 'Store in cool place',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('medicines', ['barcode' => 'BARCODE123']);
    }

    /** @test */
    public function test_medicine_create_fails_without_name()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'medicine_category_id' => $cat->id,
            'generic_id' => $gen->id,
            'medicine_unit_id' => $unit->id,
            'form' => 'tablet',
            'purchase_price' => 50,
            'sale_price' => 100,
            'tax_percent' => 0,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_medicine_create_fails_without_category()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'generic_id' => $gen->id,
            'medicine_unit_id' => $unit->id,
            'name' => 'Test Med',
            'form' => 'tablet',
            'purchase_price' => 50,
            'sale_price' => 100,
            'tax_percent' => 0,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHasErrors('medicine_category_id');
    }

    /** @test */
    public function test_medicine_create_fails_without_generic()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'medicine_category_id' => $cat->id,
            'medicine_unit_id' => $unit->id,
            'name' => 'Test Med',
            'form' => 'tablet',
            'purchase_price' => 50,
            'sale_price' => 100,
            'tax_percent' => 0,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHasErrors('generic_id');
    }

    /** @test */
    public function test_medicine_create_fails_without_unit()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'medicine_category_id' => $cat->id,
            'generic_id' => $gen->id,
            'name' => 'Test Med',
            'form' => 'tablet',
            'purchase_price' => 50,
            'sale_price' => 100,
            'tax_percent' => 0,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHasErrors('medicine_unit_id');
    }

    /** @test */
    public function test_medicine_create_fails_with_invalid_form()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'medicine_category_id' => $cat->id,
            'generic_id' => $gen->id,
            'medicine_unit_id' => $unit->id,
            'name' => 'Test Med',
            'form' => 'invalid_form',
            'purchase_price' => 50,
            'sale_price' => 100,
            'tax_percent' => 0,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHasErrors('form');
    }

    /** @test */
    public function test_medicine_create_with_negative_price_fails()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'medicine_category_id' => $cat->id,
            'generic_id' => $gen->id,
            'medicine_unit_id' => $unit->id,
            'name' => 'Neg Price',
            'form' => 'tablet',
            'purchase_price' => -50,
            'sale_price' => 100,
            'tax_percent' => 0,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHasErrors('purchase_price');
    }

    /** @test */
    public function test_medicine_create_fails_with_tax_over_100()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'medicine_category_id' => $cat->id,
            'generic_id' => $gen->id,
            'medicine_unit_id' => $unit->id,
            'name' => 'High Tax',
            'form' => 'tablet',
            'purchase_price' => 50,
            'sale_price' => 100,
            'tax_percent' => 150,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHasErrors('tax_percent');
    }

    /** @test */
    public function test_medicine_create_with_duplicate_barcode_fails()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        Medicine::factory()->create(['barcode' => 'DUP_BARCODE']);

        $response = $this->actingAs($this->admin)->post('/pharmacy/medicines', [
            'medicine_category_id' => $cat->id,
            'generic_id' => $gen->id,
            'medicine_unit_id' => $unit->id,
            'name' => 'Dup Barcode',
            'form' => 'tablet',
            'barcode' => 'DUP_BARCODE',
            'purchase_price' => 50,
            'sale_price' => 100,
            'tax_percent' => 0,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHasErrors('barcode');
    }

    /** @test */
    public function test_medicine_update()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $medicine = Medicine::factory()->create(['name' => 'Old Name']);
        $response = $this->actingAs($this->admin)->put("/pharmacy/medicines/{$medicine->id}", [
            'medicine_category_id' => $cat->id,
            'generic_id' => $gen->id,
            'medicine_unit_id' => $unit->id,
            'name' => 'Updated Name',
            'form' => 'tablet',
            'purchase_price' => 75,
            'sale_price' => 150,
            'tax_percent' => 0,
            'reorder_level' => 10,
            'reorder_quantity' => 100,
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('medicines', ['id' => $medicine->id, 'name' => 'Updated Name']);
    }

    /** @test */
    public function test_medicine_toggle_active()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $medicine = Medicine::factory()->create(['is_active' => true]);
        $response = $this->actingAs($this->admin)->patch("/pharmacy/medicines/{$medicine->id}/toggle-active");
        $response->assertSessionHas('success');
        $medicine->refresh();
        $this->assertFalse((bool)$medicine->is_active);
    }

    /** @test */
    public function test_medicine_search()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        Medicine::factory()->create(['name' => 'Searchable Medicine']);
        $response = $this->actingAs($this->admin)->get('/pharmacy/medicines-search?search=Searchable');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_medicine_show()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $medicine = Medicine::factory()->create();
        $response = $this->actingAs($this->admin)->get("/pharmacy/medicines/{$medicine->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function test_medicine_delete()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $medicine = Medicine::factory()->create();
        $response = $this->actingAs($this->admin)->delete("/pharmacy/medicines/{$medicine->id}");
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('medicines', ['id' => $medicine->id]);
    }

    /** @test */
    public function test_medicine_filter_by_category()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        Medicine::factory()->create(['medicine_category_id' => $cat->id]);
        $response = $this->actingAs($this->admin)->get('/pharmacy/medicines?category=' . $cat->id);
        $response->assertStatus(200);
    }

    /** @test */
    public function test_medicine_filter_by_form()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        Medicine::factory()->create(['form' => 'syrup']);
        $response = $this->actingAs($this->admin)->get('/pharmacy/medicines?form=syrup');
        $response->assertStatus(200);
    }

    // ========================================================================
    // PHARMACY - SUPPLIERS - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_supplier_index()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/suppliers');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_supplier_create()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/suppliers', [
            'name' => 'Test Supplier',
            'phone' => '9841234567',
            'email' => 'supplier@test.com',
            'credit_days' => 30,
            'credit_limit' => 50000,
            'opening_balance' => 0,
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', ['name' => 'Test Supplier']);
    }

    /** @test */
    public function test_supplier_create_fails_without_name()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/suppliers', [
            'credit_days' => 30,
            'credit_limit' => 0,
            'opening_balance' => 0,
        ]);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_supplier_create_fails_with_negative_credit_days()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/suppliers', [
            'name' => 'Bad Supplier',
            'credit_days' => -1,
            'credit_limit' => 0,
            'opening_balance' => 0,
        ]);
        $response->assertSessionHasErrors('credit_days');
    }

    /** @test */
    public function test_supplier_create_fails_with_credit_days_over_365()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/suppliers', [
            'name' => 'Long Credit',
            'credit_days' => 400,
            'credit_limit' => 0,
            'opening_balance' => 0,
        ]);
        $response->assertSessionHasErrors('credit_days');
    }

    /** @test */
    public function test_supplier_create_with_all_fields()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/suppliers', [
            'name' => 'Full Supplier',
            'contact_person' => 'John Doe',
            'phone' => '9841234567',
            'alternate_phone' => '9857654321',
            'email' => 'john@supplier.com',
            'address' => 'Kathmandu',
            'city' => 'Kathmandu',
            'state' => 'Bagmati',
            'country' => 'Nepal',
            'postal_code' => '44600',
            'drug_license_no' => 'DL-12345',
            'drug_license_expiry' => now()->addYear()->format('Y-m-d'),
            'pan_vat_no' => 'PAN-12345',
            'credit_days' => 45,
            'credit_limit' => 100000,
            'opening_balance' => 5000,
            'is_active' => true,
            'notes' => 'Preferred supplier',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', ['email' => 'john@supplier.com']);
    }

    /** @test */
    public function test_supplier_create_duplicate_name_fails()
    {
        Supplier::factory()->create(['name' => 'Unique Supplier']);
        $response = $this->actingAs($this->admin)->post('/pharmacy/suppliers', [
            'name' => 'Unique Supplier',
            'credit_days' => 30,
            'credit_limit' => 0,
            'opening_balance' => 0,
        ]);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_supplier_update()
    {
        $supplier = Supplier::factory()->create(['name' => 'Old Supplier Name']);
        $response = $this->actingAs($this->admin)->put("/pharmacy/suppliers/{$supplier->id}", [
            'name' => 'Updated Supplier',
            'credit_days' => 60,
            'credit_limit' => 200000,
            'opening_balance' => 10000,
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Updated Supplier']);
    }

    /** @test */
    public function test_supplier_toggle_active()
    {
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $response = $this->actingAs($this->admin)->patch("/pharmacy/suppliers/{$supplier->id}/toggle-active");
        $response->assertSessionHas('success');
        $supplier->refresh();
        $this->assertFalse((bool)$supplier->is_active);
    }

    /** @test */
    public function test_supplier_search()
    {
        Supplier::factory()->create(['name' => 'Searchable Supplier']);
        $response = $this->actingAs($this->admin)->get('/pharmacy/suppliers-search?search=Searchable');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_supplier_show()
    {
        $supplier = Supplier::factory()->create();
        $response = $this->actingAs($this->admin)->get("/pharmacy/suppliers/{$supplier->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function test_supplier_delete()
    {
        $supplier = Supplier::factory()->create();
        $response = $this->actingAs($this->admin)->delete("/pharmacy/suppliers/{$supplier->id}");
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    // ========================================================================
    // PHARMACY - PURCHASE ORDERS - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_purchase_order_create()
    {
        $supplier = Supplier::factory()->create();
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $medicine = Medicine::factory()->create();

        $response = $this->actingAs($this->admin)->post('/pharmacy/purchase-orders', [
            'supplier_id' => $supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity_ordered' => 100,
                    'unit_price' => 50,
                    'discount_percent' => 0,
                    'tax_percent' => 0,
                ],
            ],
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('purchase_orders', ['supplier_id' => $supplier->id, 'status' => 'draft']);
    }

    /** @test */
    public function test_purchase_order_create_fails_without_supplier()
    {
        [$cat, $gen, $unit] = $this->createMedicineDeps();
        $medicine = Medicine::factory()->create();
        $response = $this->actingAs($this->admin)->post('/pharmacy/purchase-orders', [
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['medicine_id' => $medicine->id, 'quantity_ordered' => 10, 'unit_price' => 50, 'discount_percent' => 0, 'tax_percent' => 0],
            ],
        ]);
        $response->assertSessionHasErrors('supplier_id');
    }

    /** @test */
    public function test_purchase_order_create_fails_without_items()
    {
        $supplier = Supplier::factory()->create();
        $response = $this->actingAs($this->admin)->post('/pharmacy/purchase-orders', [
            'supplier_id' => $supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'items' => [],
        ]);
        $response->assertSessionHasErrors('items');
    }

    /** @test */
    public function test_purchase_order_create_fails_with_invalid_expected_delivery()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $response = $this->actingAs($this->admin)->post('/pharmacy/purchase-orders', [
            'supplier_id' => $supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'expected_delivery_date' => now()->subDay()->format('Y-m-d'), // before order date
            'items' => [
                ['medicine_id' => $medicine->id, 'quantity_ordered' => 10, 'unit_price' => 50, 'discount_percent' => 0, 'tax_percent' => 0],
            ],
        ]);
        $response->assertSessionHasErrors('expected_delivery_date');
    }

    /** @test */
    public function test_purchase_order_send_and_cancel_workflow()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => 'draft',
        ]);
        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $po->id,
            'medicine_id' => $medicine->id,
        ]);

        // Send PO
        $response = $this->actingAs($this->admin)->patch("/pharmacy/purchase-orders/{$po->id}/send");
        $response->assertSessionHas('success');
        $po->refresh();
        $this->assertEquals('sent', $po->status);

        // Cancel PO
        $response = $this->actingAs($this->admin)->patch("/pharmacy/purchase-orders/{$po->id}/cancel");
        $response->assertSessionHas('success');
        $po->refresh();
        $this->assertEquals('cancelled', $po->status);
    }

    /** @test */
    public function test_purchase_order_show()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $po = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);
        PurchaseOrderItem::factory()->create(['purchase_order_id' => $po->id, 'medicine_id' => $medicine->id]);
        $response = $this->actingAs($this->admin)->get("/pharmacy/purchase-orders/{$po->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function test_purchase_order_update()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $po = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id, 'status' => 'draft']);
        $item = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $po->id,
            'medicine_id' => $medicine->id,
            'quantity_ordered' => 50,
        ]);

        $response = $this->actingAs($this->admin)->put("/pharmacy/purchase-orders/{$po->id}", [
            'supplier_id' => $supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'id' => $item->id,
                    'medicine_id' => $medicine->id,
                    'quantity_ordered' => 200,
                    'unit_price' => 45,
                    'discount_percent' => 5,
                    'tax_percent' => 0,
                ],
            ],
        ]);
        $response->assertSessionHas('success');
    }

    /** @test */
    public function test_purchase_order_delete()
    {
        $supplier = Supplier::factory()->create();
        $po = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id, 'status' => 'draft']);
        $response = $this->actingAs($this->admin)->delete("/pharmacy/purchase-orders/{$po->id}");
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('purchase_orders', ['id' => $po->id]);
    }

    // ========================================================================
    // PHARMACY - GRN (Goods Received Notes) - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_grn_create()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $po = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id, 'status' => 'sent']);
        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $po->id,
            'medicine_id' => $medicine->id,
            'quantity_ordered' => 100,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/grn', [
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'received_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => 'BATCH001',
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'quantity_received' => 100,
                    'free_quantity' => 0,
                    'unit_price' => 50,
                    'sale_price' => 100,
                    'discount_percent' => 0,
                    'tax_percent' => 0,
                ],
            ],
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('goods_received_notes', ['supplier_id' => $supplier->id, 'status' => 'pending']);
    }

    /** @test */
    public function test_grn_create_fails_without_supplier()
    {
        $medicine = Medicine::factory()->create();
        $response = $this->actingAs($this->admin)->post('/pharmacy/grn', [
            'received_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => 'BATCH001',
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'quantity_received' => 100,
                    'free_quantity' => 0,
                    'unit_price' => 50,
                    'sale_price' => 100,
                    'discount_percent' => 0,
                    'tax_percent' => 0,
                ],
            ],
        ]);
        $response->assertSessionHasErrors('supplier_id');
    }

    /** @test */
    public function test_grn_create_fails_without_batch_number()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $response = $this->actingAs($this->admin)->post('/pharmacy/grn', [
            'supplier_id' => $supplier->id,
            'received_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'quantity_received' => 100,
                    'free_quantity' => 0,
                    'unit_price' => 50,
                    'sale_price' => 100,
                    'discount_percent' => 0,
                    'tax_percent' => 0,
                ],
            ],
        ]);
        $response->assertSessionHasErrors('items.*.batch_number');
    }

    /** @test */
    public function test_grn_create_fails_without_expiry_date()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $response = $this->actingAs($this->admin)->post('/pharmacy/grn', [
            'supplier_id' => $supplier->id,
            'received_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => 'BATCH001',
                    'quantity_received' => 100,
                    'free_quantity' => 0,
                    'unit_price' => 50,
                    'sale_price' => 100,
                    'discount_percent' => 0,
                    'tax_percent' => 0,
                ],
            ],
        ]);
        $response->assertSessionHasErrors('items.*.expiry_date');
    }

    /** @test */
    public function test_grn_verify_and_post_workflow()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $po = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id, 'status' => 'sent']);
        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $po->id,
            'medicine_id' => $medicine->id,
            'quantity_ordered' => 100,
        ]);

        $this->actingAs($this->admin)->post('/pharmacy/grn', [
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'received_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => 'BATCH_VERIFY',
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'quantity_received' => 100,
                    'free_quantity' => 0,
                    'unit_price' => 50,
                    'sale_price' => 100,
                    'discount_percent' => 0,
                    'tax_percent' => 0,
                ],
            ],
        ]);

        $grn = GoodsReceivedNote::first();

        // Verify GRN
        $response = $this->actingAs($this->admin)->patch("/pharmacy/grn/{$grn->id}/verify");
        $response->assertSessionHas('success');
        $grn->refresh();
        $this->assertEquals('verified', $grn->status);

        // Post GRN
        $response = $this->actingAs($this->admin)->patch("/pharmacy/grn/{$grn->id}/post");
        $response->assertSessionHas('success');
        $grn->refresh();
        $this->assertEquals('posted', $grn->status);

        $this->assertDatabaseHas('stock_batches', ['batch_number' => 'BATCH_VERIFY']);
    }

    /** @test */
    public function test_grn_show()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $grn = GoodsReceivedNote::factory()->create(['supplier_id' => $supplier->id]);
        GrnItem::factory()->create([
            'goods_received_note_id' => $grn->id,
            'medicine_id' => $medicine->id,
        ]);
        $response = $this->actingAs($this->admin)->get("/pharmacy/grn/{$grn->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function test_grn_delete()
    {
        $supplier = Supplier::factory()->create();
        $grn = GoodsReceivedNote::factory()->create(['supplier_id' => $supplier->id, 'status' => 'pending']);
        $response = $this->actingAs($this->admin)->delete("/pharmacy/grn/{$grn->id}");
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('goods_received_notes', ['id' => $grn->id]);
    }

    // ========================================================================
    // PHARMACY - INVENTORY & STOCK - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_inventory_index()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/inventory');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_inventory_batch_detail()
    {
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create(['medicine_id' => $medicine->id]);
        $response = $this->actingAs($this->admin)->get("/pharmacy/inventory/batch/{$batch->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function test_stock_adjustment_addition()
    {
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'quantity_available' => 100,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/inventory/adjust', [
            'medicine_id' => $medicine->id,
            'stock_batch_id' => $batch->id,
            'adjustment_type' => 'addition',
            'quantity' => 50,
            'reason' => 'Stock count correction',
        ]);
        $response->assertSessionHas('success');
        $batch->refresh();
        $this->assertEquals(150, $batch->quantity_available);
    }

    /** @test */
    public function test_stock_adjustment_deduction()
    {
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'quantity_available' => 100,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/inventory/adjust', [
            'medicine_id' => $medicine->id,
            'stock_batch_id' => $batch->id,
            'adjustment_type' => 'deduction',
            'quantity' => 30,
            'reason' => 'Damaged goods',
        ]);
        $response->assertSessionHas('success');
        $batch->refresh();
        $this->assertEquals(70, $batch->quantity_available);
    }

    /** @test */
    public function test_stock_adjustment_expired()
    {
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'quantity_available' => 50,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/inventory/adjust', [
            'medicine_id' => $medicine->id,
            'stock_batch_id' => $batch->id,
            'adjustment_type' => 'expired',
            'quantity' => 50,
            'reason' => 'Past expiry date',
        ]);
        $response->assertSessionHas('success');
        $batch->refresh();
        $this->assertEquals(0, $batch->quantity_available);
    }

    /** @test */
    public function test_stock_adjustment_fails_without_reason()
    {
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create(['medicine_id' => $medicine->id]);
        $response = $this->actingAs($this->admin)->post('/pharmacy/inventory/adjust', [
            'medicine_id' => $medicine->id,
            'stock_batch_id' => $batch->id,
            'adjustment_type' => 'deduction',
            'quantity' => 10,
        ]);
        $response->assertSessionHasErrors('reason');
    }

    /** @test */
    public function test_stock_adjustment_fails_with_negative_quantity()
    {
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create(['medicine_id' => $medicine->id]);
        $response = $this->actingAs($this->admin)->post('/pharmacy/inventory/adjust', [
            'medicine_id' => $medicine->id,
            'stock_batch_id' => $batch->id,
            'adjustment_type' => 'deduction',
            'quantity' => -10,
            'reason' => 'Test negative',
        ]);
        $response->assertSessionHasErrors('quantity');
    }

    // ========================================================================
    // PHARMACY - POS / SALES - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_sales_index()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/sales');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_sales_create()
    {
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'quantity_available' => 100,
            'sale_price' => 100,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/sales', [
            'sale_date' => now()->format('Y-m-d'),
            'sale_type' => 'counter',
            'discount_type' => 'percent',
            'discount_value' => 0,
            'payment_mode' => 'cash',
            'paid_amount' => 200,
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 2,
                    'unit_price' => 100,
                    'discount_percent' => 0,
                    'tax_percent' => 0,
                ],
            ],
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('sales', ['payment_mode' => 'cash']);
    }

    /** @test */
    public function test_sales_create_with_prescription()
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['nmc_number' => 'NMC_SAL1', 'phone' => '9841111111']);
        $visit = Visit::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create([
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);
        $prescription = Prescription::factory()->create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'quantity_available' => 100,
            'sale_price' => 100,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/sales', [
            'sale_date' => now()->format('Y-m-d'),
            'sale_type' => 'prescription',
            'prescription_id' => $prescription->id,
            'discount_type' => 'percent',
            'discount_value' => 10,
            'payment_mode' => 'cash',
            'paid_amount' => 180,
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 2,
                    'unit_price' => 100,
                    'discount_percent' => 0,
                    'tax_percent' => 0,
                ],
            ],
        ]);
        $response->assertSessionHas('success');
    }

    /** @test */
    public function test_sales_create_fails_without_items()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/sales', [
            'sale_date' => now()->format('Y-m-d'),
            'sale_type' => 'counter',
            'discount_type' => 'percent',
            'discount_value' => 0,
            'payment_mode' => 'cash',
            'paid_amount' => 0,
            'items' => [],
        ]);
        $response->assertSessionHasErrors('items');
    }

    /** @test */
    public function test_sales_create_fails_with_empty_items_array()
    {
        $response = $this->actingAs($this->admin)->post('/pharmacy/sales', [
            'sale_date' => now()->format('Y-m-d'),
            'sale_type' => 'counter',
            'discount_type' => 'percent',
            'discount_value' => 0,
            'payment_mode' => 'cash',
            'paid_amount' => 0,
            'items' => [],
        ]);
        $response->assertSessionHasErrors('items');
    }

    /** @test */
    public function test_sales_create_fails_with_invalid_sale_type()
    {
        $medicine = Medicine::factory()->create();
        $response = $this->actingAs($this->admin)->post('/pharmacy/sales', [
            'sale_date' => now()->format('Y-m-d'),
            'sale_type' => 'invalid_type',
            'discount_type' => 'percent',
            'discount_value' => 0,
            'payment_mode' => 'cash',
            'paid_amount' => 0,
            'items' => [
                ['medicine_id' => $medicine->id, 'quantity' => 1, 'unit_price' => 100, 'discount_percent' => 0, 'tax_percent' => 0],
            ],
        ]);
        $response->assertSessionHasErrors('sale_type');
    }

    /** @test */
    public function test_sales_create_fails_with_invalid_payment_mode()
    {
        $medicine = Medicine::factory()->create();
        $response = $this->actingAs($this->admin)->post('/pharmacy/sales', [
            'sale_date' => now()->format('Y-m-d'),
            'sale_type' => 'counter',
            'discount_type' => 'percent',
            'discount_value' => 0,
            'payment_mode' => 'invalid',
            'paid_amount' => 0,
            'items' => [
                ['medicine_id' => $medicine->id, 'quantity' => 1, 'unit_price' => 100, 'discount_percent' => 0, 'tax_percent' => 0],
            ],
        ]);
        $response->assertSessionHasErrors('payment_mode');
    }

    /** @test */
    public function test_sales_check_interactions()
    {
        $generic1 = Generic::factory()->create();
        $generic2 = Generic::factory()->create();
        DrugInteraction::factory()->create([
            'generic_id_1' => $generic1->id,
            'generic_id_2' => $generic2->id,
            'severity' => 'major',
            'description' => 'Serious interaction',
        ]);

        $medicine1 = Medicine::factory()->create(['generic_id' => $generic1->id]);
        $medicine2 = Medicine::factory()->create(['generic_id' => $generic2->id]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/sales/check-interactions', [
            'items' => [
                ['medicine_id' => $medicine1->id],
                ['medicine_id' => $medicine2->id],
            ],
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['interactions']);
    }

    /** @test */
    public function test_sales_suggest_batches()
    {
        $medicine = Medicine::factory()->create();
        $batch1 = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH_A',
            'expiry_date' => now()->addMonths(6),
            'quantity_available' => 50,
        ]);
        $batch2 = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH_B',
            'expiry_date' => now()->addMonths(12),
            'quantity_available' => 100,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/sales/suggest-batches', [
            'medicine_id' => $medicine->id,
            'quantity' => 30,
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([['batch_id', 'batch_number', 'quantity_available', 'expiry_date', 'suggested_quantity']]);
    }

    /** @test */
    public function test_sales_show()
    {
        $medicine = Medicine::factory()->create();
        $sale = Sales::factory()->create();
        SalesItem::factory()->create(['sale_id' => $sale->id, 'medicine_id' => $medicine->id]);
        $response = $this->actingAs($this->admin)->get("/pharmacy/sales/{$sale->id}");
        $response->assertStatus(200);
    }

    // ========================================================================
    // PHARMACY - RETURNS - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_sales_return_create()
    {
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'quantity_available' => 50,
            'quantity_sold' => 10,
        ]);
        $sale = Sales::factory()->create(['status' => 'completed']);
        $saleItem = SalesItem::factory()->create([
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
            'stock_batch_id' => $batch->id,
            'quantity' => 5,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/sales-returns', [
            'sale_id' => $sale->id,
            'return_date' => now()->format('Y-m-d'),
            'reason' => 'wrong_medicine',
            'refund_mode' => 'cash',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity_returned' => 2,
                    'stock_action' => 'return_to_stock',
                ],
            ],
        ]);
        $response->assertSessionHas('success');
    }

    /** @test */
    public function test_sales_return_create_fails_without_reason()
    {
        $sale = Sales::factory()->create();
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create(['medicine_id' => $medicine->id]);
        $saleItem = SalesItem::factory()->create([
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
            'stock_batch_id' => $batch->id,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/sales-returns', [
            'sale_id' => $sale->id,
            'return_date' => now()->format('Y-m-d'),
            'refund_mode' => 'cash',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity_returned' => 1,
                    'stock_action' => 'return_to_stock',
                ],
            ],
        ]);
        $response->assertSessionHasErrors('reason');
    }

    /** @test */
    public function test_sales_return_create_fails_with_invalid_reason()
    {
        $sale = Sales::factory()->create();
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create(['medicine_id' => $medicine->id]);
        $saleItem = SalesItem::factory()->create([
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
            'stock_batch_id' => $batch->id,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/sales-returns', [
            'sale_id' => $sale->id,
            'return_date' => now()->format('Y-m-d'),
            'reason' => 'invalid_reason',
            'refund_mode' => 'cash',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity_returned' => 1,
                    'stock_action' => 'return_to_stock',
                ],
            ],
        ]);
        $response->assertSessionHasErrors('reason');
    }

    /** @test */
    public function test_supplier_return_create()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'quantity_available' => 100,
        ]);

        $response = $this->actingAs($this->admin)->post('/pharmacy/supplier-returns', [
            'supplier_id' => $supplier->id,
            'return_date' => now()->format('Y-m-d'),
            'reason' => 'damaged',
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'stock_batch_id' => $batch->id,
                    'quantity' => 10,
                    'unit_price' => 50,
                ],
            ],
        ]);
        $response->assertSessionHas('success');
    }

    /** @test */
    public function test_supplier_return_complete()
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create();
        $batch = StockBatch::factory()->create([
            'medicine_id' => $medicine->id,
            'quantity_available' => 50,
        ]);

        $this->actingAs($this->admin)->post('/pharmacy/supplier-returns', [
            'supplier_id' => $supplier->id,
            'return_date' => now()->format('Y-m-d'),
            'reason' => 'damaged',
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'stock_batch_id' => $batch->id,
                    'quantity' => 10,
                    'unit_price' => 50,
                ],
            ],
        ]);

        $supplierReturn = SupplierReturn::first();
        $response = $this->actingAs($this->admin)->patch("/pharmacy/supplier-returns/{$supplierReturn->id}/complete");
        $response->assertSessionHas('success');
        $supplierReturn->refresh();
        $this->assertEquals('completed', $supplierReturn->status);
    }

    // ========================================================================
    // PHARMACY - DRUG INTERACTIONS - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_drug_interaction_create()
    {
        $generic1 = Generic::factory()->create();
        $generic2 = Generic::factory()->create();

        $response = $this->actingAs($this->admin)->post('/pharmacy/drug-interactions', [
            'generic_id_1' => $generic1->id,
            'generic_id_2' => $generic2->id,
            'severity' => 'major',
            'description' => 'Increased risk of bleeding',
            'management' => 'Monitor closely',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('drug_interactions', [
            'generic_id_1' => $generic1->id,
            'generic_id_2' => $generic2->id,
        ]);
    }

    /** @test */
    public function test_drug_interaction_update()
    {
        $generic1 = Generic::factory()->create();
        $generic2 = Generic::factory()->create();
        $interaction = DrugInteraction::factory()->create([
            'generic_id_1' => $generic1->id,
            'generic_id_2' => $generic2->id,
        ]);

        $response = $this->actingAs($this->admin)->put("/pharmacy/drug-interactions/{$interaction->id}", [
            'generic_id_1' => $generic1->id,
            'generic_id_2' => $generic2->id,
            'severity' => 'contraindicated',
            'description' => 'Contraindicated - do not use together',
        ]);
        $response->assertSessionHas('success');
        $interaction->refresh();
        $this->assertEquals('contraindicated', $interaction->severity);
    }

    /** @test */
    public function test_drug_interaction_delete()
    {
        $interaction = DrugInteraction::factory()->create();
        $response = $this->actingAs($this->admin)->delete("/pharmacy/drug-interactions/{$interaction->id}");
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('drug_interactions', ['id' => $interaction->id]);
    }

    // ========================================================================
    // PHARMACY - PRESCRIPTIONS (Pharmacy Side) - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_pharmacy_prescription_index()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/prescriptions');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_pharmacy_prescription_create()
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['nmc_number' => 'NMC_PRE1', 'phone' => '9841111111']);

        $response = $this->actingAs($this->admin)->post('/pharmacy/prescriptions', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'medicine_name' => 'Paracetamol',
                    'dosage_instruction' => '500mg',
                    'frequency' => 'twice_daily',
                    'duration_days' => 5,
                    'quantity_prescribed' => 10,
                ],
            ],
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('prescriptions', ['patient_id' => $patient->id]);
    }

    /** @test */
    public function test_pharmacy_prescription_show()
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['nmc_number' => 'NMC_PRE2', 'phone' => '9841111111']);
        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);
        PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);
        $response = $this->actingAs($this->admin)->get("/pharmacy/prescriptions/{$prescription->id}");
        $response->assertStatus(200);
    }

    // ========================================================================
    // LABORATORY MODULE - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_lab_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/laboratory/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_lab_test_parameter_create()
    {
        $category = LabTestCategory::factory()->create();
        $test = LabTest::factory()->create(['lab_test_category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->post('/laboratory/test-parameters', [
            'lab_test_id' => $test->id,
            'name' => 'Hemoglobin',
            'unit' => 'g/dL',
            'reference_range' => '13.5-17.5',
            'display_order' => 1,
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('lab_test_parameters', ['name' => 'Hemoglobin']);
    }

    /** @test */
    public function test_lab_test_parameter_create_fails_without_test_id()
    {
        $response = $this->actingAs($this->admin)->post('/laboratory/test-parameters', [
            'name' => 'Test Param',
        ]);
        $response->assertSessionHasErrors('lab_test_id');
    }

    /** @test */
    public function test_lab_test_parameter_create_fails_without_name()
    {
        $category = LabTestCategory::factory()->create();
        $test = LabTest::factory()->create(['lab_test_category_id' => $category->id]);
        $response = $this->actingAs($this->admin)->post('/laboratory/test-parameters', [
            'lab_test_id' => $test->id,
        ]);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_lab_test_parameter_update()
    {
        $param = LabTestParameter::factory()->create(['name' => 'Old Param']);
        $response = $this->actingAs($this->admin)->put("/laboratory/test-parameters/{$param->id}", [
            'lab_test_id' => $param->lab_test_id,
            'name' => 'Updated Param',
            'display_order' => 2,
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('lab_test_parameters', ['id' => $param->id, 'name' => 'Updated Param']);
    }

    /** @test */
    public function test_lab_test_parameter_delete()
    {
        $param = LabTestParameter::factory()->create();
        $response = $this->actingAs($this->admin)->delete("/laboratory/test-parameters/{$param->id}");
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('lab_test_parameters', ['id' => $param->id]);
    }

    /** @test */
    public function test_lab_orders_index()
    {
        $response = $this->actingAs($this->admin)->get('/laboratory/orders');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_lab_order_show()
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['nmc_number' => 'NMC_LAB1', 'phone' => '9841111111']);
        $visit = Visit::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create([
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);
        $labOrder = LabOrder::factory()->create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);
        $response = $this->actingAs($this->admin)->get("/laboratory/orders/{$labOrder->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function test_lab_order_collect_sample()
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['nmc_number' => 'NMC_LAB2', 'phone' => '9841111111']);
        $visit = Visit::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create(['visit_id' => $visit->id, 'patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $labOrder = LabOrder::factory()->create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'ordered',
        ]);

        $response = $this->actingAs($this->admin)->patch("/laboratory/orders/{$labOrder->id}/collect-sample");
        $response->assertSessionHas('success');
        $labOrder->refresh();
        $this->assertEquals('sample_collected', $labOrder->status);
    }

    /** @test */
    public function test_lab_order_start_processing()
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['nmc_number' => 'NMC_LAB3', 'phone' => '9841111111']);
        $visit = Visit::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create(['visit_id' => $visit->id, 'patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $labOrder = LabOrder::factory()->create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'sample_collected',
        ]);

        $response = $this->actingAs($this->admin)->patch("/laboratory/orders/{$labOrder->id}/start-processing");
        $response->assertSessionHas('success');
        $labOrder->refresh();
        $this->assertEquals('processing', $labOrder->status);
    }

    /** @test */
    public function test_lab_order_complete()
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['nmc_number' => 'NMC_LAB4', 'phone' => '9841111111']);
        $visit = Visit::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create(['visit_id' => $visit->id, 'patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $labOrder = LabOrder::factory()->create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'processing',
        ]);

        $response = $this->actingAs($this->admin)->patch("/laboratory/orders/{$labOrder->id}/complete");
        $response->assertSessionHas('success');
        $labOrder->refresh();
        $this->assertEquals('completed', $labOrder->status);
    }

    /** @test */
    public function test_lab_results_create()
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['nmc_number' => 'NMC_LAB5', 'phone' => '9841111111']);
        $visit = Visit::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create(['visit_id' => $visit->id, 'patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        $labOrder = LabOrder::factory()->create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'processing',
        ]);
        $category = LabTestCategory::factory()->create();
        $test = LabTest::factory()->create(['lab_test_category_id' => $category->id]);
        $orderItem = LabOrderItem::factory()->create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $test->id,
            'status' => 'processing',
        ]);
        $param = LabTestParameter::factory()->create(['lab_test_id' => $test->id]);

        $response = $this->actingAs($this->admin)->post("/laboratory/orders/{$labOrder->id}/results", [
            'results' => [
                [
                    'lab_order_item_id' => $orderItem->id,
                    'lab_test_parameter_id' => $param->id,
                    'result_value' => '15.2',
                ],
            ],
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('lab_results', ['result_value' => '15.2']);
    }

    /** @test */
    public function test_lab_results_index()
    {
        $response = $this->actingAs($this->admin)->get('/laboratory/results');
        $response->assertStatus(200);
    }

    // ========================================================================
    // REPORTS MODULE - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_clinic_reports_index()
    {
        $response = $this->actingAs($this->admin)->get('/reports');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_reports_appointments()
    {
        $response = $this->actingAs($this->admin)->get('/reports/appointments');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_reports_revenue()
    {
        $patient = Patient::factory()->create();
        Invoice::factory()->create(['patient_id' => $patient->id, 'total' => 5000, 'status' => 'paid']);
        $response = $this->actingAs($this->admin)->get('/reports/revenue');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_reports_patients()
    {
        Patient::factory()->count(5)->create();
        $response = $this->actingAs($this->admin)->get('/reports/patients');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_reports_csv_export()
    {
        $response = $this->actingAs($this->admin)->get('/reports/appointments/export');
        $response->assertStatus(200);
    }

    // ========================================================================
    // PHARMACY REPORTS MODULE - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_pharmacy_reports_index()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/reports');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_pharmacy_reports_sales()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/reports/sales');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_pharmacy_reports_stock()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/reports/stock');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_pharmacy_reports_expiry()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/reports/expiry');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_pharmacy_reports_purchases()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/reports/purchases');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_pharmacy_reports_slow_moving()
    {
        $response = $this->actingAs($this->admin)->get('/pharmacy/reports/slow-moving');
        $response->assertStatus(200);
    }

    // ========================================================================
    // PROFILE MODULE - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_profile_page()
    {
        $response = $this->actingAs($this->admin)->get('/profile');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_profile_update()
    {
        $response = $this->actingAs($this->admin)->patch('/profile', [
            'name' => 'Updated Admin',
            'email' => 'admin@gmail.com',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'name' => 'Updated Admin']);
    }

    /** @test */
    public function test_profile_update_fails_with_invalid_email()
    {
        $response = $this->actingAs($this->admin)->patch('/profile', [
            'name' => 'Admin',
            'email' => 'not-an-email',
        ]);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function test_profile_update_fails_without_name()
    {
        $response = $this->actingAs($this->admin)->patch('/profile', [
            'email' => 'admin@gmail.com',
        ]);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_password_update()
    {
        $response = $this->actingAs($this->admin)->put('/password', [
            'current_password' => 'Admin@123',
            'password' => 'NewPass@123',
            'password_confirmation' => 'NewPass@123',
        ]);
        $response->assertSessionHas('success');
    }

    /** @test */
    public function test_password_update_fails_with_wrong_current()
    {
        $response = $this->actingAs($this->admin)->put('/password', [
            'current_password' => 'WrongPass',
            'password' => 'NewPass@123',
            'password_confirmation' => 'NewPass@123',
        ]);
        $response->assertSessionHasErrors('current_password');
    }

    /** @test */
    public function test_password_update_fails_with_mismatch_confirmation()
    {
        $response = $this->actingAs($this->admin)->put('/password', [
            'current_password' => 'Admin@123',
            'password' => 'NewPass@123',
            'password_confirmation' => 'DifferentPass',
        ]);
        $response->assertSessionHasErrors('password');
    }

    // ========================================================================
    // AUTHORIZATION & SECURITY - EXHAUSTIVE TESTS
    // ========================================================================

    /** @test */
    public function test_unauthenticated_access_to_pharmacy()
    {
        $response = $this->get('/pharmacy');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_unauthenticated_access_to_laboratory()
    {
        $response = $this->get('/laboratory/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_unauthenticated_access_to_billing()
    {
        $response = $this->get('/billing/invoices');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_unauthenticated_access_to_dues()
    {
        $response = $this->get('/dues');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_unauthenticated_access_to_reports()
    {
        $response = $this->get('/reports');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_unauthenticated_access_to_consultations()
    {
        $response = $this->get('/consultations');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_mass_assignment_protection()
    {
        $response = $this->actingAs($this->admin)->post('/patients', [
            'name' => 'Mass Assign',
            'phone' => '9841234567',
            'id' => 99999,
            'created_at' => '2020-01-01',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('patients', ['id' => 99999]);
    }

    /** @test */
    public function test_csrf_protection_on_logout()
    {
        // Should require CSRF for state-changing requests
        // Without CSRF, it should fail with 419 or similar
        $response = $this->post('/logout', [], ['X-CSRF-TOKEN' => 'invalid']);
        $response->assertStatus(419);
    }

    /** @test */
    public function test_doctor_dashboard_access()
    {
        $response = $this->actingAs($this->doctor)->get('/doctor/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_doctor_todays_visits()
    {
        $response = $this->actingAs($this->doctor)->get('/doctor/visits/today');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_doctor_consultations()
    {
        $response = $this->actingAs($this->doctor)->get('/doctor/consultations');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_doctor_profile()
    {
        $response = $this->actingAs($this->doctor)->get('/doctor/profile');
        $response->assertStatus(200);
    }
}
