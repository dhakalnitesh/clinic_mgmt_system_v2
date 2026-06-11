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
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('medicine_id')->constrained()->restrictOnDelete();
            $table->foreignId('stock_batch_id')->constrained()->restrictOnDelete();

            $table->integer('quantity_returned');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);  // refund amount for this item

            // Whether this returned stock goes back to shelf or written off
            $table->enum('stock_action', [
                'return_to_stock',  // good condition — put back in stock_batch
                'write_off',        // damaged / expired — do NOT restock
            ])->default('return_to_stock');

            $table->string('condition_note', 200)->nullable();
            $table->timestamps();
            $table->index('sales_return_id');
            $table->index('sale_item_id');
            $table->index('stock_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};
