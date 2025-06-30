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
        Schema::create('piggy_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('piggy_bank_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', [
                'starting_amount',
                'manual_add',
                'manual_withdraw',
                'scheduled_add',
                // Add more types if you want in the future!
            ]);
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->date('scheduled_for')->nullable(); // Only for scheduled transactions, ignore for manual
            $table->timestamps();

            $table->foreign('piggy_bank_id')->references('id')->on('piggy_banks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piggy_bank_transactions');
    }
};
