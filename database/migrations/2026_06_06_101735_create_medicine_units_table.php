<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();         // e.g. Tablet, Capsule, Syrup
            $table->string('abbreviation', 20)->unique(); // it like the code  e.g. Tab, Cap, mL
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_units');
    }
};