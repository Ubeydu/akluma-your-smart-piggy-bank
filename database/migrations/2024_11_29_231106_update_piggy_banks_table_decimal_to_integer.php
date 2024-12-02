<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            // First drop the columns we want to modify
            $table->dropColumn(['price', 'starting_amount', 'balance', 'currency']);

            // Add them back with new types
            $table->integer('price')->after('name');
            $table->integer('starting_amount')->default(0)->after('details');
            $table->integer('balance')->default(0)->after('image');  // Changed from storedAs to default
            $table->string('currency', 3)->default(config('app.default_currency', 'TRY'))->after('balance');
        });
    }

    public function down(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->dropColumn(['price', 'starting_amount', 'balance', 'currency']);

            // Restore original columns
            $table->decimal('price', 12, 2);
            $table->decimal('starting_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('currency', 3);
        });
    }
};
