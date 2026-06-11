<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();

            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();

            $table->text('chief_complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('visited_at')->nullable();

            $table->enum('visit_type', ['follow_up', 'walk_in'])->default('walk_in')->index();
            $table->enum('status', [
                'waiting',
                'vitals_pending',
               
                'in_consultation',
                'completed',
                'cancelled'
            ])->default('waiting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
