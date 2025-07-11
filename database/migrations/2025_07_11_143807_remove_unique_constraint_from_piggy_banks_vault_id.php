<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign('piggy_banks_vault_id_foreign');
            // Drop the unique constraint
            $table->dropUnique('piggy_banks_vault_id_unique');
            // Recreate the foreign key without unique constraint
            $table->foreign('vault_id')->references('id')->on('vaults')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign('piggy_banks_vault_id_foreign');
            // Add back the unique constraint
            $table->unique('vault_id', 'piggy_banks_vault_id_unique');
            // Recreate the foreign key
            $table->foreign('vault_id')->references('id')->on('vaults')->onDelete('set null');
        });
    }
};
