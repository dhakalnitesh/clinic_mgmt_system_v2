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
      Schema::create('doctors', function (Blueprint $table) {
    $table->id(); 
    $table->string('name'); 
    // add later Employee ID, Department
// Qualification
// Experience Years, User ID,is_active, Email, doctor_code, license_number is same as nmc number??
// We can later make the specialization table so that all the doctor 
    $table->string('nmc_number')->unique(); 
    $table->string('specialization')->nullable(); 
    $table->string('phone')->nullable(); 
    $table->decimal('consultation_fee', 10, 2)->nullable(); 
    $table->string('photo')->nullable(); 
    $table->string('address1')->nullable();
    $table->string('address2')->nullable();
    $table->string('notes')->nullable();
    $table->timestamps(); 
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
