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
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->string('type')->default('scheduled')->after('id');
            $table->string('chosen_strategy')->nullable()->change();
            $table->string('selected_frequency')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->string('chosen_strategy')->nullable(false)->change();
            $table->string('selected_frequency')->nullable(false)->change();
        });
    }
};
