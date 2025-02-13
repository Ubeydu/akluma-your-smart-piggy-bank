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
        DB::table('scheduled_savings')
            ->whereNull('notification_statuses')
            ->orWhereNull('notification_attempts')
            ->update([
                'notification_statuses' => json_encode([
                    'email' => ['sent' => false, 'sent_at' => null],
                    'sms' => ['sent' => false, 'sent_at' => null],
                    'push' => ['sent' => false, 'sent_at' => null]
                ]),
                'notification_attempts' => json_encode([
                    'email' => 0,
                    'sms' => 0,
                    'push' => 0
                ])
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('scheduled_savings')
            ->update([
                'notification_statuses' => null,
                'notification_attempts' => null
            ]);
    }
};
