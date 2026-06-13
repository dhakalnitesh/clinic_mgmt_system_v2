<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('tax_percent', 5, 2)->default(0)->after('discount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_percent');
            $table->date('due_date')->nullable()->after('tax_amount');
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['tax_percent', 'tax_amount', 'due_date', 'refunded_amount']);
        });
    }
};
