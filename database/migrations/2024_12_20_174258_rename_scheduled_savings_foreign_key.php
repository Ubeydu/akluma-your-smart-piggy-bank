<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            // Just add the new foreign key constraint
            $table->foreign('piggy_bank_id', 'scheduled_savings_piggy_bank_id_foreign')
                ->references('id')
                ->on('piggy_banks')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            $table->dropForeign('scheduled_savings_piggy_bank_id_foreign');
        });
    }
};
