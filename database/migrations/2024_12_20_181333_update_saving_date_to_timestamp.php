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
            $table->dropColumn('saving_date');
            $table->timestamp('saving_date')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            $table->dropColumn('saving_date');
            $table->date('saving_date')->after('status');
        });
    }
};
