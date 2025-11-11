<?php

use App\Models\PiggyBank;
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
        // Add the remaining_amount column
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->decimal('remaining_amount', 12, 2)->nullable()->after('uptodate_final_total');
        });

        // Backfill existing piggy banks with calculated remaining_amount
        // Process in chunks to avoid memory issues
        PiggyBank::chunk(100, function ($piggyBanks) {
            foreach ($piggyBanks as $piggyBank) {
                // Calculate remaining_amount using the existing accessor logic
                $projectedTotal = $piggyBank->uptodate_final_total ?? $piggyBank->final_total;
                $actualTotal = $piggyBank->transactions()->sum('amount');
                $remainingAmount = $projectedTotal - $actualTotal;

                // Update quietly to avoid triggering events during migration
                $piggyBank->updateQuietly([
                    'remaining_amount' => $remainingAmount,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->dropColumn('remaining_amount');
        });
    }
};
