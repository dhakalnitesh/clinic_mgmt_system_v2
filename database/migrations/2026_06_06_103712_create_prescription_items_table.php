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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prescription_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('medicine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('medicine_name')->nullable();
            $table->foreignId('generic_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('frequency', [
                'once_daily',
                'twice_daily',
                'thrice_daily',
                'four_times_daily',
                'every_6_hours',
                'every_8_hours',
                'every_12_hours',
                'as_needed',
                'at_bedtime',
                'before_meals',
                'after_meals',
                'other',
            ])->nullable();


            $table->string('duration_days')->nullable();
            // e.g. 5 days
            $table->string('route', 50)->nullable(); // oral, topical, IV, IM, etc.
            $table->integer('quantity_prescribed')->nullable();
            //eg. 10 tablets/ 1 Bottle 
            $table->integer('quantity_dispensed')->default(0);

            $table->boolean('is_substitutable')->default(true); // generic substitution allowed
            $table->enum('status', ['pending', 'partial', 'dispensed'])->default('pending');
            $table->text('dosage_instruction')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
