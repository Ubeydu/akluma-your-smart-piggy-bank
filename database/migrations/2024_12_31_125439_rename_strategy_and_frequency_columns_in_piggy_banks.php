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
            $table->renameColumn('saving_strategy', 'chosen_strategy');
            $table->renameColumn('saving_frequency', 'selected_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->renameColumn('chosen_strategy', 'saving_strategy');
            $table->renameColumn('selected_frequency', 'saving_frequency');
        });
    }
};
