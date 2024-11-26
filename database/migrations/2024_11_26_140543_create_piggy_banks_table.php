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
        Schema::create('piggy_banks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->string('link')->nullable();
            $table->text('details')->nullable();
            $table->decimal('starting_amount', 12, 2)->default(0);
            $table->string('image')->default('images/piggy_banks/default_piggy_bank.png');
            $table->string('currency', 3);
            $table->decimal('balance', 12, 2)->default(0);
            $table->date('date');
            $table->enum('status', ['active', 'paused', 'done', 'cancelled'])->default('active');
        });

        DB::statement("ALTER TABLE piggy_banks ADD CONSTRAINT chk_details_length CHECK ( CHAR_LENGTH(details) <= 5000)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piggy_banks');
    }
};
