<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            // First drop the columns we're changing
            $table->dropColumn(['initial_deposit', 'current_balance', 'target_amount']);
            // Add new columns with proper positioning
            $table->decimal('price', 12, 2)->after('name');
            $table->decimal('starting_amount', 12, 2)->nullable()->after('price');
            $table->decimal('current_balance', 12, 2)->nullable()->after('starting_amount');
            $table->decimal('target_amount', 12, 2)->after('current_balance');
            $table->decimal('extra_savings', 12, 2)->nullable()->after('target_amount');
            $table->decimal('total_savings', 12, 2)->after('extra_savings');

        });
    }

    public function down(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->dropColumn(['price', 'starting_amount', 'current_balance', 'target_amount', 'extra_savings', 'total_savings']);
            $table->decimal('target_amount', 12, 2)->after('name')->nullable(false);
            $table->decimal('initial_deposit', 12, 2)->after('target_amount')->default(0);
            $table->decimal('current_balance', 12, 2)->after('initial_deposit')->default(0);
        });
    }

};
