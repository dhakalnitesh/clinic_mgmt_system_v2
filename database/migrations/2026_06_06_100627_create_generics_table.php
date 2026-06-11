<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generics', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();       // INN / generic drug name
            $table->string('pharmacological_class')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_controlled')->default(false); // controlled substance
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generics');
    }
};