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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestamp('prescribed_at')->useCurrent();
            // Links to clinic module — nullable so pharmacy can work standalone
            $table->foreignId('patient_id')
                ->nullable()
                ->constrained('patients')
                ->nullOnDelete();

            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedBigInteger('encounter_id')->nullable(); // OPD/IPD encounter
            $table->string('prescription_number', 30)->unique(); // RX-2024-00001

            $table->enum('status', [
                'pending',    // not yet dispensed
                'partial',    // partially dispensed
                'dispensed',  // fully dispensed
                'cancelled',
            ])->default('pending');

            $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dispensed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->longText('advices')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'prescribed_at']);
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('encounter_id');

            $table->index('consultation_id');
            $table->index('prescribed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
