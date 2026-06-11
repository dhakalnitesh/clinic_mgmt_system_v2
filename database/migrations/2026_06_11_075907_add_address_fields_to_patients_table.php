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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('citizenship_type')->nullable()->after('gender');
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete()->after('citizenship_type');
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete()->after('province_id');
            $table->foreignId('municipal_id')->nullable()->constrained()->nullOnDelete()->after('district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['municipal_id']);
            $table->dropColumn(['citizenship_type', 'province_id', 'district_id', 'municipal_id']);
        });
    }
};
