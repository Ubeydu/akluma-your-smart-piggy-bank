<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            $table->dropForeign('scheduled_savings_new_piggy_bank_id_foreign');

            // Add the foreign key with the correct name using constraint() method
            $table->foreign('piggy_bank_id', 'scheduled_savings_piggy_bank_id_foreign')
                ->references('id')
                ->on('piggy_banks')
                ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            $table->dropForeign('scheduled_savings_piggy_bank_id_foreign');

            // Restore the previous constraint
            $table->foreign('piggy_bank_id', 'scheduled_savings_new_piggy_bank_id_foreign')
                ->references('id')
                ->on('piggy_banks')
                ->cascadeOnDelete();
        });
    }
};
