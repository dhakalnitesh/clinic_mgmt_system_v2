<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('goods_received_note_id')->nullable()->constrained()->nullOnDelete();

            $table->string('batch_number', 80);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date');

            $table->integer('quantity_received');          // original qty received
            $table->integer('quantity_available');         // current remaining stock
            $table->integer('quantity_sold')->default(0);
            $table->integer('quantity_adjusted')->default(0); // write-offs, corrections

            $table->decimal('purchase_price', 10, 2);     // cost price per unit
            $table->decimal('sale_price', 10, 2);          // selling price per unit
            $table->decimal('mrp', 10, 2)->nullable();

            $table->boolean('is_active')->default(true);  // false when batch exhausted

            $table->timestamps();

            // Composite index for FEFO dispensing: medicine + expiry ASC
            $table->index(['medicine_id', 'expiry_date', 'is_active']);
            $table->index(['medicine_id', 'quantity_available']);
            $table->unique(['medicine_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};