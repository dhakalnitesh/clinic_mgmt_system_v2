<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique(); // INV-2024-00001

            // Patient & prescription links (nullable for OTC sales)
    $table->foreignId('patient_id')
                ->nullable()
                ->constrained('patients')
                ->nullOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();

            $table->date('sale_date');
            $table->enum('sale_type', [
                'counter',      // walk-in OTC sale
                'prescription', // dispensing against a prescription
                'opd',          // linked to an OPD encounter
                'ipd',          // linked to an IPD admission
            ])->default('counter');

            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('pharmacist_id')->nullable()->constrained('users')->nullOnDelete();

            // Amounts
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->enum('discount_type', ['percent', 'amount'])->default('percent');
            $table->decimal('discount_value', 8, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);

            // Payment
            $table->enum('payment_mode', [
                'cash', 'card', 'insurance', 'credit', 'upi', 'bank_transfer', 'mixed','e-sewa','khalti','fonepay',
            ])->default('cash');
            $table->string('payment_reference', 100)->nullable();

            $table->enum('status', [
                'draft',
                'completed',
                'returned',
                'partial_return',
            ])->default('draft');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sale_date']);
            $table->index('patient_id');
            $table->index(['cashier_id', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};