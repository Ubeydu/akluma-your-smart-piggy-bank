<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        // First, create a new table with desired structure
        Schema::create('scheduled_savings_new', function (Blueprint $table) {
            // Define columns in desired order
            $table->id();
            $table->foreignId('piggy_bank_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('saving_number');
            $table->decimal('amount', 19, 2);
            $table->enum('status', ['saved', 'pending'])
                ->default('pending');
            $table->timestamp('saving_date');
            $table->timestamps();
        });

        // Drop the old table
        Schema::dropIfExists('scheduled_savings');

        // Rename new table to original name
        Schema::rename('scheduled_savings_new', 'scheduled_savings');
    }

    public function down(): void {
        Schema::create('scheduled_savings_old', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('piggy_bank_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('saving_number');
            $table->bigInteger('amount');
            $table->enum('status', ['saved', 'pending', 'snoozed'])
                ->default('pending');
            $table->timestamp('saving_date');
        });

        Schema::dropIfExists('scheduled_savings');
        Schema::rename('scheduled_savings_old', 'scheduled_savings');
    }
};
