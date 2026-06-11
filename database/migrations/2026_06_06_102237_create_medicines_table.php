<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_category_id')
                  ->constrained()
                  ->restrictOnDelete();
            $table->foreignId('generic_id')
                  ->constrained()
                  ->restrictOnDelete();
            $table->foreignId('medicine_unit_id')
                  ->constrained()
                  ->restrictOnDelete();

            // Identity
            $table->string('name', 200);                  // Brand / trade name
            $table->string('strength', 80)->nullable();   // e.g. 500mg, 250mg/5mL
            $table->enum('form', [
                'tablet', 'capsule', 'syrup', 'suspension',
                'injection', 'cream', 'ointment', 'gel',
                'drops', 'inhaler', 'patch', 'suppository',
                'powder', 'lotion', 'solution', 'other',
            ])->default('tablet');
            $table->string('manufacturer', 150)->nullable();
            $table->string('barcode', 100)->nullable()->unique();
            $table->string('hsn_code', 20)->nullable();   // for GST/tax

            // Pricing
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('mrp', 10, 2)->nullable();    // max retail price
            $table->decimal('tax_percent', 5, 2)->default(0);

            // Stock control
            $table->integer('reorder_level')->default(10);    // alert when stock falls below
            $table->integer('reorder_quantity')->default(100); // suggested order qty
            $table->string('shelf_location', 50)->nullable();  // rack / shelf reference

            // Clinical flags
            $table->boolean('is_prescription_required')->default(false);
            $table->boolean('is_controlled')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
            $table->index('generic_id');
            $table->index('medicine_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};