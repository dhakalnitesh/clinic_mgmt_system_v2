<?php

use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Consultation\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Doctor\DoctorController;
use App\Http\Controllers\FollowUp\FollowUpController;
use App\Http\Controllers\Laboratory\LabDashboardController;
use App\Http\Controllers\Laboratory\LabOrderController;
use App\Http\Controllers\Laboratory\LabResultController;
use App\Http\Controllers\Laboratory\LabTestParameterController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Pharmacy\DashboardController as PharmacyDashboardController;
use App\Http\Controllers\Pharmacy\DrugInteractionController;
use App\Http\Controllers\Pharmacy\GenericController;
use App\Http\Controllers\Pharmacy\GrnController;
use App\Http\Controllers\Pharmacy\InventoryController;
use App\Http\Controllers\Pharmacy\MedicineCategoryController;
use App\Http\Controllers\Pharmacy\MedicineController;
use App\Http\Controllers\Pharmacy\MedicineUnitController;
use App\Http\Controllers\Pharmacy\PrescriptionController;
use App\Http\Controllers\Pharmacy\PurchaseOrderController;
use App\Http\Controllers\Pharmacy\ReportController;
use App\Http\Controllers\Pharmacy\SalesController;
use App\Http\Controllers\Pharmacy\SalesReturnController;
use App\Http\Controllers\Pharmacy\SupplierController;
use App\Http\Controllers\Pharmacy\SupplierReturnController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Report\ReportController as ClinicReportController;
use App\Http\Controllers\Visit\VisitController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// ─── DOCTOR ROUTES (all auth, with per-route role guard) ──────
Route::middleware(['auth'])->group(function () {

    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::get('/doctors/create', [DoctorController::class, 'create'])->name('doctors.create')->middleware('role:admin');
    Route::post('/doctors', [DoctorController::class, 'store'])->name('doctors.store')->middleware('role:admin');
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])->name('doctors.show');
    Route::get('/doctors/{doctor}/edit', [DoctorController::class, 'edit'])->name('doctors.edit')->middleware('role:admin');
    Route::match(['put', 'patch'], '/doctors/{doctor}', [DoctorController::class, 'update'])->name('doctors.update')->middleware('role:admin');
    Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy'])->name('doctors.destroy')->middleware('role:admin');
});

// ─── GUEST (NON-ADMIN) FLOW ────────────────────────────────────
Route::middleware(['auth'])->prefix('guest')->name('guest.')->group(function () {
    Route::get('/patients/create', [\App\Http\Controllers\Guest\PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [\App\Http\Controllers\Guest\PatientController::class, 'store'])->name('patients.store');
    Route::get('/appointments/create', [\App\Http\Controllers\Guest\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [\App\Http\Controllers\Guest\AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/doctors/available', [\App\Http\Controllers\Guest\AppointmentController::class, 'getDoctorsByDate'])->name('doctors.available');
});

// ─── ADMIN-ONLY ROUTES ─────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Patients
    Route::resource('/patients', PatientController::class)->except(['show']);
    Route::get('/patients/{patient}/history', [PatientController::class, 'show'])
        ->name('patients.show');

    // Appointments
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])
        ->name('appointments.updateStatus');
    Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])
        ->name('appointments.cancel');
    Route::resource('/appointments', AppointmentController::class);

    // Visits
    Route::resource('/visits', VisitController::class);
    Route::patch('/visits/{visit}/cancel', [VisitController::class, 'cancel']);

    // Follow-ups
    Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
    Route::post('/follow-ups', [FollowUpController::class, 'store'])->name('follow-ups.store');
    Route::patch('/follow-ups/{followUp}/complete', [FollowUpController::class, 'complete'])
        ->name('follow-ups.complete');
    Route::patch('/follow-ups/{followUp}/cancel', [FollowUpController::class, 'cancel'])
        ->name('follow-ups.cancel');

    // Consultations
    Route::get('/consultations/create/{visit}', [ConsultationController::class, 'create'])
        ->name('consultations.create');
    Route::resource('/consultations', ConsultationController::class);
    Route::get('/prescriptions/{prescription}/print', [PrescriptionController::class, 'print'])
        ->name('prescriptions.print');
    Route::resource('prescriptions', PrescriptionController::class);
    Route::prefix('consultations')->group(function () {
        Route::get('/{consultation}/prescription', [PrescriptionController::class, 'show']);
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('laboratory')->name('laboratory.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [LabDashboardController::class, 'index'])
        ->name('dashboard');

    // Orders
    Route::get('/orders', [LabOrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/orders/{labOrder}', [LabOrderController::class, 'show'])
        ->name('orders.show');

    Route::patch(
        '/orders/{labOrder}/collect-sample',
        [LabOrderController::class, 'collectSample']
    )->name('orders.collect-sample');

    Route::patch(
        '/orders/{labOrder}/start-processing',
        [LabOrderController::class, 'startProcessing']
    )->name('orders.start-processing');

    Route::patch(
        '/orders/{labOrder}/complete',
        [LabOrderController::class, 'complete']
    )->name('orders.complete');

    // Results (nested under orders)
    Route::get('/orders/{labOrder}/results', [LabResultController::class, 'create'])
        ->name('orders.results.create');

    Route::post('/orders/{labOrder}/results', [LabResultController::class, 'store'])
        ->name('orders.results.store');

    // Results index (standalone)
    Route::get('/results', [LabResultController::class, 'index'])
        ->name('results.index');

    // Printable result report
    Route::get('/orders/{labOrder}/results/print', [LabResultController::class, 'print'])
        ->name('orders.results.print');

    // CSV export
    Route::get('/orders/{labOrder}/results/export/csv', [LabResultController::class, 'exportCsv'])
        ->name('orders.results.export.csv');

    // PDF export
    Route::get('/orders/{labOrder}/results/export/pdf', [LabResultController::class, 'exportPdf'])
        ->name('orders.results.export.pdf');

    // Test Parameters
    Route::resource('test-parameters', LabTestParameterController::class)
        ->except(['show']);
});




Route::prefix('pharmacy')
    ->name('pharmacy.')
    ->middleware(['auth', 'verified', 'role:admin'])
    ->group(function () {
 
        // ── Dashboard ──────────────────────────────────────────────
        Route::get('/', [PharmacyDashboardController::class, 'index'])
             ->name('dashboard');
 
        // ── Drug Master ────────────────────────────────────────────
        Route::resource('medicines', MedicineController::class);
        Route::patch('medicines/{medicine}/toggle-active',
             [MedicineController::class, 'toggleActive'])->name('medicines.toggle-active');
        Route::get('medicines-search',
             [MedicineController::class, 'search'])->name('medicines.search');
 
        // ── Settings: Reference Data ───────────────────────────────
        Route::resource('categories', MedicineCategoryController::class)
             ->except(['show']);
        Route::resource('generics', GenericController::class)
             ->except(['show']);
        Route::resource('units', MedicineUnitController::class)
             ->except(['show']);
 
        // ── Inventory ──────────────────────────────────────────────
        Route::get('inventory', [InventoryController::class, 'index'])
             ->name('inventory.index');
        Route::get('inventory/batch/{batch}', [InventoryController::class, 'batchDetail'])
             ->name('inventory.batch');
        Route::post('inventory/adjust', [InventoryController::class, 'adjust'])
             ->name('inventory.adjust');
 
        // ── Suppliers ──────────────────────────────────────────────
        Route::resource('suppliers', SupplierController::class);
        Route::patch('suppliers/{supplier}/toggle-active',
             [SupplierController::class, 'toggleActive'])->name('suppliers.toggle-active');
        Route::get('suppliers-search',
             [SupplierController::class, 'search'])->name('suppliers.search');
 
        // ── Purchase Orders ────────────────────────────────────────
        Route::resource('purchase-orders', PurchaseOrderController::class);
        Route::patch('purchase-orders/{purchaseOrder}/send',
             [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
        Route::patch('purchase-orders/{purchaseOrder}/cancel',
             [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
 
        // ── GRN (Goods Received Notes) ─────────────────────────────
        Route::get('grn', [GrnController::class, 'index'])
             ->name('grn.index');
        Route::get('grn/create', [GrnController::class, 'create'])
             ->name('grn.create');
        Route::post('grn', [GrnController::class, 'store'])
             ->name('grn.store');
        Route::get('grn/{grn}', [GrnController::class, 'show'])
             ->name('grn.show');
        Route::patch('grn/{grn}/verify', [GrnController::class, 'verify'])
             ->name('grn.verify');
        Route::patch('grn/{grn}/post', [GrnController::class, 'post'])
             ->name('grn.post');
        Route::delete('grn/{grn}', [GrnController::class, 'destroy'])
             ->name('grn.destroy');
 
        // ── Sales / POS ────────────────────────────────────────────
        Route::get('sales', [SalesController::class, 'index'])
             ->name('sales.index');
        Route::get('sales/create', [SalesController::class, 'create'])
             ->name('sales.create');
        Route::post('sales', [SalesController::class, 'store'])
             ->name('sales.store');
        Route::get('sales/{sale}', [SalesController::class, 'show'])
             ->name('sales.show');
        Route::post('sales/check-interactions', [SalesController::class, 'checkInteractions'])
             ->name('sales.check-interactions');
        Route::post('sales/suggest-batches', [SalesController::class, 'suggestBatches'])
             ->name('sales.suggest-batches');
 
        // ── Sales Returns ──────────────────────────────────────────
        Route::get('sales/{sale}/return', [SalesReturnController::class, 'create'])
             ->name('sales.return.create');
        Route::post('sales-returns', [SalesReturnController::class, 'store'])
             ->name('sales-returns.store');
 
        // ── Supplier Returns ───────────────────────────────────────
        Route::get('supplier-returns', [SupplierReturnController::class, 'index'])
             ->name('supplier-returns.index');
        Route::get('supplier-returns/create', [SupplierReturnController::class, 'create'])
             ->name('supplier-returns.create');
        Route::post('supplier-returns', [SupplierReturnController::class, 'store'])
             ->name('supplier-returns.store');
        Route::get('supplier-returns/{supplierReturn}', [SupplierReturnController::class, 'show'])
             ->name('supplier-returns.show');
        Route::patch('supplier-returns/{supplierReturn}/complete',
             [SupplierReturnController::class, 'complete'])->name('supplier-returns.complete');
 
        // ── Prescriptions ──────────────────────────────────────────
        Route::resource('prescriptions', PrescriptionController::class)
             ->only(['index', 'create', 'store', 'show']);
 
        // ── Drug Interactions ──────────────────────────────────────
        Route::resource('drug-interactions', DrugInteractionController::class)
             ->except(['show']);
 
        // ── Reports ────────────────────────────────────────────────
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/',           [ReportController::class, 'index'])   ->name('index');
            Route::get('sales',       [ReportController::class, 'sales'])   ->name('sales');
            Route::get('stock',       [ReportController::class, 'stock'])   ->name('stock');
            Route::get('expiry',      [ReportController::class, 'expiry'])  ->name('expiry');
            Route::get('purchases',   [ReportController::class, 'purchases'])->name('purchases');
            Route::get('slow-moving', [ReportController::class, 'slowMoving'])->name('slow-moving');
        });
    });





// Billing
Route::middleware(['auth', 'role:admin'])->prefix('billing')->name('billing.')->group(function () {
    Route::get('/invoices', [BillingController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/print-all', [BillingController::class, 'printAllInvoices'])->name('invoices.print-all');
    Route::get('/payments', [BillingController::class, 'payments'])->name('payments');
    Route::post('/invoices', [BillingController::class, 'storeInvoice'])->name('invoices.store');
    Route::patch('/invoices/{invoice}/pay', [BillingController::class, 'pay'])->name('invoices.pay');
    Route::patch('/invoices/{invoice}', [BillingController::class, 'updateInvoice'])->name('invoices.update');
    Route::patch('/invoices/{invoice}/cancel', [BillingController::class, 'cancelInvoice'])->name('invoices.cancel');
    Route::post('/invoices/{invoice}/refund', [BillingController::class, 'refund'])->name('invoices.refund');
    Route::delete('/invoices/{invoice}', [BillingController::class, 'destroyInvoice'])->name('invoices.destroy');
    Route::get('/payments/{payment}/receipt', [BillingController::class, 'paymentReceipt'])->name('payments.receipt');
    Route::get('/payments/patient/{patient}', [BillingController::class, 'patientPaymentHistory'])->name('payments.patient');
    Route::get('/invoices/{invoice}/print', [BillingController::class, 'printInvoice'])->name('invoices.print');
});

// Due Management
Route::get('/dues', [\App\Http\Controllers\Due\DueController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('dues.index');

// Clinic Reports
Route::middleware(['auth', 'role:admin'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ClinicReportController::class, 'index'])->name('index');
    Route::get('/appointments', [ClinicReportController::class, 'appointments'])->name('appointments');
    Route::get('/revenue', [ClinicReportController::class, 'revenue'])->name('revenue');
    Route::get('/patients', [ClinicReportController::class, 'patients'])->name('patients');

    // CSV Exports
    Route::get('/appointments/export', [ClinicReportController::class, 'exportAppointments'])->name('appointments.export');
    Route::get('/revenue/export', [ClinicReportController::class, 'exportRevenue'])->name('revenue.export');
    Route::get('/patients/export', [ClinicReportController::class, 'exportPatients'])->name('patients.export');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
