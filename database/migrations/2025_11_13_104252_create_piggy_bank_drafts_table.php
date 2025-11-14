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
        Schema::create('piggy_bank_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('email')->nullable(); // For Issue #234 - guest user drafts
            $table->string('name'); // Piggy bank name for display in list
            $table->string('currency', 3); // ISO currency code

            // Strategy context - using varchar to match piggy_banks table
            $table->string('strategy'); // 'pick-date' or 'enter-saving-amount'
            $table->string('frequency'); // 'days', 'weeks', 'months', 'years'

            // All creation data stored as JSON
            $table->json('step1_data'); // Product info, price, starting_amount, preview
            $table->json('step3_data'); // Strategy-specific calculations
            $table->json('payment_schedule'); // Generated schedule

            // Summary for quick display in list
            $table->decimal('price', 12, 2); // Match piggy_banks table precision
            $table->string('preview_image')->default('images/piggy_banks/default_piggy_bank.png');

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('email'); // For Issue #234
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piggy_bank_drafts');
    }
};
