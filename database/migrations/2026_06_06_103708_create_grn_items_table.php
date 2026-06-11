<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_received_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->nullOnDelete();

            // Batch details (captured at receiving time)
            $table->string('batch_number', 80);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date');

            // Quantities
            $table->integer('quantity_received');
            $table->integer('free_quantity')->default(0); // bonus/free stock from supplier

            // Pricing
            $table->decimal('unit_price', 10, 2);
            $table->decimal('sale_price', 10, 2)->default(0); // selling price at time of GRN
            $table->decimal('mrp', 10, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);

            // Set after GRN is posted — links to the created stock batch
            $table->foreignId('stock_batch_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index('goods_received_note_id');
            $table->index('medicine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};