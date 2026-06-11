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

        //     id
        // visit_id
        // patient_id
        // doctor_id
        // ...

        // (Keep these because consultation is the primary medical record.)
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('visit_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('appointment_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('patient_id')
                ->constrained()
                ->cascadeOnDelete();

            // Actual treating doctor
            $table->foreignId('doctor_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->dateTime('consulted_at')->nullable();

            // Medical details
            $table->text('chief_complaint')->nullable();
            $table->longText('history')->nullable();
            $table->longText('examination_notes')->nullable();
            $table->longText('diagnosis')->nullable();
            $table->longText('notes')->nullable();

            $table->date('follow_up_date')->nullable();

            // Consultation status
            $table->enum('consultation_status', [
                'draft',
                'in_progress',
                'completed',
                'review_pending',
                'signed',
                'cancelled'
            ])->default('draft');
            // Audit fields
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamps();

            // Optional indexes for performance
            $table->index('consultation_status');
            $table->index('consulted_at');
            $table->index('follow_up_date');
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('visit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
