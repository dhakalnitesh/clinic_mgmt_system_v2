<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('grn_number', 30)->unique();     // GRN-2024-00001
            $table->date('received_date');
            $table->string('invoice_number', 80)->nullable(); // supplier's invoice no
            $table->date('invoice_date')->nullable();

            $table->enum('status', [
                'pending',   // received but not yet stock-posted
                'verified',  // checked, ready to post
                'posted',    // stock has been updated in batches
            ])->default('pending');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'received_date']);
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_received_notes');
    }
};