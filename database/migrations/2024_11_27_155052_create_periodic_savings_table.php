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
        Schema::create('periodic_savings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('piggy_bank_id')->constrained()->onDelete('cascade');
            $table->date('payment_due_date');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['paid', 'unpaid', 'snoozed'])->default('unpaid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodic_savings');
    }
};
