<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
                Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('returned_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
 
            $table->string('return_number', 30)->unique(); // SR-2024-00001
 
            // Patient link (carried from original sale)
            $table->unsignedBigInteger('patient_id')->nullable();
 
            $table->date('return_date');
 
            $table->enum('reason', [
                'wrong_medicine',
                'wrong_quantity',
                'adverse_reaction',
                'prescription_changed',
                'patient_refused',
                'duplicate_sale',
                'other',
            ]);
 
            $table->enum('status', [
                'draft',
                'approved',
                'completed',
                'cancelled',
            ])->default('draft');
 
            // Refund details
            $table->decimal('total_return_amount', 12, 2)->default(0);
            $table->enum('refund_mode', [
                'cash', 'card', 'bank_transfer', 'credit_note', 'upi',
            ])->default('cash');
            $table->string('refund_reference', 100)->nullable();
            $table->boolean('refund_processed')->default(false);
            $table->timestamp('refunded_at')->nullable();
 
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['status', 'return_date']);
            $table->index('sale_id');
            $table->index('patient_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
