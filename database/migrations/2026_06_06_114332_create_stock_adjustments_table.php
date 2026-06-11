<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->restrictOnDelete();
            $table->foreignId('stock_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('adjusted_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('adjustment_type', [
                'addition',    // stock added (e.g. opening stock)
                'deduction',   // manual deduction
                'expired',     // removed due to expiry
                'damaged',     // damaged stock write-off
                'theft',       // theft/shortage
                'correction',  // physical count correction
                'return_to_supplier', // returned to supplier
            ]);

            // Positive = stock added, Negative = stock removed
            $table->integer('quantity');
            $table->string('reference_number', 80)->nullable(); // audit reference
            $table->text('reason');

            $table->timestamps();

            $table->index(['medicine_id', 'adjustment_type']);
            $table->index('adjusted_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};