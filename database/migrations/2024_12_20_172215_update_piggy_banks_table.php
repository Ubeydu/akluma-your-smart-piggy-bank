<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            // Drop existing columns we'll replace
            $table->dropColumn([
                'purchase_date',
                'price',
                'starting_amount',
                'balance'
            ]);

            // Add new columns - using bigInteger for money values
            $table->bigInteger('target_amount')->after('details');
            $table->bigInteger('initial_deposit')->default(0)->after('target_amount');
            $table->bigInteger('current_balance')->default(0)->after('initial_deposit');
            $table->string('saving_strategy')->default('pick-date')->after('current_balance');
            $table->string('saving_frequency')->nullable()->after('saving_strategy');
        });
    }

    public function down(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->date('purchase_date');
            $table->integer('price');
            $table->integer('starting_amount')->default(0);
            $table->integer('balance')->default(0);

            $table->dropColumn([
                'target_amount',
                'initial_deposit',
                'current_balance',
                'saving_strategy',
                'saving_frequency'
            ]);
        });
    }
};
