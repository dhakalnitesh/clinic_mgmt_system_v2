<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drug_interactions', function (Blueprint $table) {
            $table->id();

            // Interactions are stored at the GENERIC level (not brand)
            // so checking "Paracetamol + Warfarin" catches all brand combinations
            $table->foreignId('generic_id_1')->constrained('generics')->restrictOnDelete();
            $table->foreignId('generic_id_2')->constrained('generics')->restrictOnDelete();

            $table->enum('severity', [
                'minor',           // minimal clinical effect
                'moderate',        // monitor closely
                'major',           // potentially life-threatening
                'severe',          // severe interaction
                'contraindicated', // absolutely avoid
            ]);

            $table->text('description');            // what happens when combined
            $table->text('management')->nullable(); // clinical management guidance
            $table->string('reference')->nullable(); // literature reference

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Prevent duplicates — (A,B) and (B,A) should both work via query
            $table->unique(['generic_id_1', 'generic_id_2']);
            $table->index(['generic_id_1', 'severity']);
            $table->index(['generic_id_2', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drug_interactions');
    }
};