<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('appointments', function (Blueprint $table) {

    $table->id();

    $table->foreignId('patient_id')
        ->constrained('patients')
        ->restrictOnDelete();

    $table->foreignId('doctor_id')
        ->constrained('doctors')
        ->restrictOnDelete();

    $table->string('appointment_date')->nullable()->index();
    $table->time('appointment_time')->nullable();

    // $table->enum('status', [
    //     'waiting',
    //     'confirmed',
    //     'checked_in',
    //     'completed',
    //     'cancelled',
    //     'postponed'
    // ])->default('waiting')->index();
       $table->enum('status', [
        'waiting',
        'visited',
    ])->default('waiting')->index();

    $table->text('reasons')->nullable();

    $table->foreignId('receptionist_id')
        ->nullable()
        ->constrained('users')
        ->restrictOnDelete();

    $table->timestamps();

    $table->unique(['patient_id','doctor_id', 'appointment_date']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};