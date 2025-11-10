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
        Schema::table('scheduled_savings', function (Blueprint $table) {
            // Add 'archived' column (boolean, nullable, defaults to false)
            // - null/false = active schedule item
            // - true = archived (old schedule item from previous version)
            $table->boolean('archived')->nullable()->default(false)->after('status');

            // Add 'recalculation_version' column (unsigned integer, defaults to 1)
            // - Version 1 = original schedule
            // - Version 2+ = recalculated schedules
            // - Increments each time user recalculates
            $table->unsignedInteger('recalculation_version')->default(1)->after('archived');

            // Add index for efficient querying of active schedules
            $table->index(['piggy_bank_id', 'archived', 'status'], 'idx_piggy_bank_archived_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            // Drop the index first
            $table->dropIndex('idx_piggy_bank_archived_status');

            // Drop the columns
            $table->dropColumn(['archived', 'recalculation_version']);
        });
    }
};
