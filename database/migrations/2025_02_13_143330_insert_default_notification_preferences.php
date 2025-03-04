<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('piggy_banks')
            ->orderBy('id')
            ->chunk(100, function ($piggyBanks) {
                foreach ($piggyBanks as $piggyBank) {
                    DB::table('notification_preferences')->insert([
                        'piggy_bank_id' => $piggyBank->id,
                        'channel_preferences' => json_encode([
                            'email' => ['enabled' => true],
                            'sms' => ['enabled' => true],
                            'push' => ['enabled' => true]
                        ]),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('notification_preferences')->truncate();
    }
};
