<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            $table->timestamp('last_modified_at')->nullable()->after('status');
            $table->index('last_modified_at');
        });

        // Backfill existing rows with their updated_at value
        DB::table('scheduled_savings')->update(['last_modified_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            $table->dropIndex(['last_modified_at']);
            $table->dropColumn('last_modified_at');
        });
    }
};
