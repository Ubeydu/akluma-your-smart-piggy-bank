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
        Schema::create('user_dashboard_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('stat_type'); // 'total_saved', 'left_to_save', etc
            $table->json('currency_breakdown'); // {"XAF": 45000, "GBP": 4900, "TRY": 129000}
            $table->string('period')->default('current'); // 'current', 'monthly', 'daily'
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['user_id', 'stat_type', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_stats');
    }
};
