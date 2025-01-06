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
        // First rename the table
        Schema::rename('periodic_savings', 'scheduled_savings');

        // Then modify the structure
        Schema::table('scheduled_savings', function (Blueprint $table) {
            // Rename payment_due_date to saving_date
            $table->renameColumn('payment_due_date', 'saving_date');

            // Add saving_number column
            $table->integer('saving_number')->after('piggy_bank_id');

            // Change amount to bigInteger for consistent money handling
            $table->dropColumn('amount');
            $table->bigInteger('amount')->after('saving_number');

            // Modify status enum
            $table->dropColumn('status');
            $table->enum('status', ['saved', 'pending', 'snoozed'])
                ->default('pending')
                ->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            $table->renameColumn('saving_date', 'payment_due_date');

            $table->dropColumn('amount');
            $table->decimal('amount', 12, 2)->after('saving_number');

            $table->dropColumn(['saving_number', 'status']);
            $table->enum('status', ['paid', 'unpaid', 'snoozed'])
                ->default('unpaid');
        });

        Schema::rename('scheduled_savings', 'periodic_savings');
    }
};
