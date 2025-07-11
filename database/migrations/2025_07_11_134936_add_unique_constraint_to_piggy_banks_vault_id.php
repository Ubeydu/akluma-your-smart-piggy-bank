<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->unique('vault_id', 'piggy_banks_vault_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->dropUnique('piggy_banks_vault_id_unique');
        });
    }
};
