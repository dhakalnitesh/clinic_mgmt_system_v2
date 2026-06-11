<?php

use App\Http\Controllers\Pharmacy\MedicineController;
use App\Http\Controllers\Pharmacy\InventoryController;
use App\Http\Controllers\Pharmacy\SaleController;
use App\Http\Controllers\Pharmacy\SalesController;
// Phase 3 controllers (added here so Ziggy routes exist — implementations built in Phase 3)
// use App\Http\Controllers\Pharmacy\SupplierController;
// use App\Http\Controllers\Pharmacy\PurchaseOrderController;
// use App\Http\Controllers\Pharmacy\GrnController;

// Phase 4 controllers
// use App\Http\Controllers\Pharmacy\PrescriptionController;
// use App\Http\Controllers\Pharmacy\SalesReturnController;

use Illuminate\Support\Facades\Route;

Route::prefix('pharmacy')
    ->name('pharmacy.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        // ── Drug Master ────────────────────────────────────────────
        Route::resource('medicines', MedicineController::class);
        Route::patch('medicines/{medicine}/toggle-active', [MedicineController::class, 'toggleActive'])
             ->name('medicines.toggle-active');
        Route::get('medicines-search', [MedicineController::class, 'search'])
             ->name('medicines.search');

        // ── Inventory ──────────────────────────────────────────────
        Route::get('inventory', [InventoryController::class, 'index'])
             ->name('inventory.index');
        Route::get('inventory/batch/{batch}', [InventoryController::class, 'batchDetail'])
             ->name('inventory.batch');
        Route::post('inventory/adjust', [InventoryController::class, 'adjust'])
             ->name('inventory.adjust');

        // ── Sales (stub — full POS built in Phase 4) ───────────────
        Route::get('sales/{sale}', [SalesController::class, 'show'])
             ->name('sales.show');
    });