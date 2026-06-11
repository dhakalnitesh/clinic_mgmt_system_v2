<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('goods_received_note_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('returned_by')->constrained('users')->restrictOnDelete();

            $table->string('return_number', 30)->unique(); // SR-2024-00001
            $table->date('return_date');

            $table->enum('reason', [
                'expired', 'damaged', 'excess', 'wrong_item', 'quality_issue', 'other',
            ])->default('damaged');

            $table->enum('status', ['draft', 'sent', 'completed', 'cancelled'])->default('draft');

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'return_date']);
            $table->index('supplier_id');
        });


    }

    public function down(): void
    {
       
        Schema::dropIfExists('supplier_returns');
    }
};