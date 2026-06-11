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
        Schema::create('vitals', function (Blueprint $table) {

            $table->id();
            $table->foreignId('consultation_id')->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('patient_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('appointment_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('visit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();



            // Vital Signs
            $table->string('blood_pressure')->nullable();

            $table->unsignedInteger('pulse')->nullable();

            $table->decimal('temperature', 5, 2)->nullable();

            $table->unsignedInteger('oxygen')->nullable();

            $table->decimal('height', 5, 2)->nullable();

            $table->decimal('weight', 5, 2)->nullable();

            $table->unsignedInteger('respiratory_rate')->nullable();

            $table->decimal('bmi', 5, 2)->nullable();

            $table->decimal('blood_sugar', 6, 2)->nullable();

            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('visit_id');
            $table->index('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vitals');
    }
};
