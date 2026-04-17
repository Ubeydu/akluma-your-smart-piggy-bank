<?php

namespace Database\Seeders;

use App\Models\PiggyBank;
use App\Models\PiggyBankTransaction;
use App\Models\ScheduledSaving;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ──────────────────────────────────────────

        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'currency' => 'EUR',
            'language' => 'en',
            'timezone' => 'Europe/Paris',
        ]);

        $adminUser = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'currency' => 'USD',
            'language' => 'en',
        ]);

        User::factory()->googleOAuth()->create([
            'name' => 'Google User',
            'email' => 'google-user@example.com',
            'currency' => 'EUR',
            'language' => 'en',
        ]);

        // ── Piggy Banks for test@example.com ───────────────

        // PB #1: Vacation Fund (scheduled, active, EUR, weekly)
        // 12 savings x 150 = 1800 + 200 starting = 2000 target
        $pb1 = PiggyBank::create([
            'user_id' => $testUser->id,
            'name' => 'Vacation Fund',
            'type' => 'scheduled',
            'status' => 'active',
            'currency' => 'EUR',
            'chosen_strategy' => 'enter-saving-amount',
            'selected_frequency' => 'weeks',
            'target_amount' => 2000,
            'starting_amount' => 200,
            'price' => 2000,
            'total_savings' => 1800,
            'final_total' => 2000,
            'uptodate_final_total' => null,
            'remaining_amount' => 1350,
        ]);

        $savings1 = $this->createSchedule($pb1, 150, 'weeks', savedCount: 3, pendingCount: 9);
        $this->transaction($pb1, $testUser, 'starting_amount', 200);
        foreach (array_slice($savings1, 0, 3) as $s) {
            $this->transaction($pb1, $testUser, 'scheduled_add', 150, $s->saving_date);
        }

        // PB #2: New Laptop (scheduled, active, EUR, monthly)
        // 6 savings x 250 = 1500 target
        $pb2 = PiggyBank::create([
            'user_id' => $testUser->id,
            'name' => 'New Laptop',
            'type' => 'scheduled',
            'status' => 'active',
            'currency' => 'EUR',
            'chosen_strategy' => 'enter-saving-amount',
            'selected_frequency' => 'months',
            'target_amount' => 1500,
            'starting_amount' => 0,
            'price' => 1500,
            'total_savings' => 1500,
            'final_total' => 1500,
            'uptodate_final_total' => null,
            'remaining_amount' => 1250,
        ]);

        $savings2 = $this->createSchedule($pb2, 250, 'months', savedCount: 1, pendingCount: 5);
        $this->transaction($pb2, $testUser, 'scheduled_add', 250, $savings2[0]->saving_date);

        // PB #3: Emergency Fund (scheduled, paused, USD, weekly, pick-date)
        // 20 savings x 225 = 4500 + 500 starting = 5000 target
        $pb3 = PiggyBank::create([
            'user_id' => $testUser->id,
            'name' => 'Emergency Fund',
            'type' => 'scheduled',
            'status' => 'paused',
            'currency' => 'USD',
            'chosen_strategy' => 'pick-date',
            'selected_frequency' => 'weeks',
            'target_amount' => 5000,
            'starting_amount' => 500,
            'price' => 5000,
            'total_savings' => 4500,
            'final_total' => 5000,
            'uptodate_final_total' => null,
            'remaining_amount' => 3375,
        ]);

        $savings3 = $this->createSchedule($pb3, 225, 'weeks', savedCount: 5, pendingCount: 15);
        $this->transaction($pb3, $testUser, 'starting_amount', 500);
        foreach (array_slice($savings3, 0, 5) as $s) {
            $this->transaction($pb3, $testUser, 'scheduled_add', 225, $s->saving_date);
        }

        // PB #4: Birthday Gift (scheduled, done, GBP, weekly)
        // 6 savings x 50 = 300 target, all completed
        $pb4 = PiggyBank::create([
            'user_id' => $testUser->id,
            'name' => 'Birthday Gift',
            'type' => 'scheduled',
            'status' => 'done',
            'currency' => 'GBP',
            'chosen_strategy' => 'enter-saving-amount',
            'selected_frequency' => 'weeks',
            'target_amount' => 300,
            'starting_amount' => 0,
            'price' => 300,
            'total_savings' => 300,
            'final_total' => 300,
            'uptodate_final_total' => null,
            'remaining_amount' => 0,
            'actual_completed_at' => now()->subWeek(),
        ]);

        $savings4 = $this->createSchedule($pb4, 50, 'weeks', savedCount: 6, pendingCount: 0);
        foreach ($savings4 as $s) {
            $this->transaction($pb4, $testUser, 'scheduled_add', 50, $s->saving_date);
        }

        // PB #5: Old Project (scheduled, cancelled, EUR, monthly)
        // Had recalculations before cancellation
        $pb5 = PiggyBank::create([
            'user_id' => $testUser->id,
            'name' => 'Old Project',
            'type' => 'scheduled',
            'status' => 'cancelled',
            'currency' => 'EUR',
            'chosen_strategy' => 'enter-saving-amount',
            'selected_frequency' => 'months',
            'target_amount' => 10000,
            'starting_amount' => 100,
            'price' => 10000,
            'total_savings' => 9900,
            'final_total' => 10000,
            'uptodate_final_total' => 2100,
            'remaining_amount' => 1000,
        ]);

        // Archived schedule (version 2) — replaced by a recalculation
        foreach (range(1, 4) as $i) {
            ScheduledSaving::create([
                'piggy_bank_id' => $pb5->id,
                'saving_number' => $i,
                'amount' => 600,
                'status' => 'pending',
                'saving_date' => now()->subMonths(7 - $i),
                'archived' => true,
                'recalculation_version' => 2,
            ]);
        }

        // Active schedule (version 3) — current at time of cancellation
        $savings5 = $this->createSchedule($pb5, 500, 'months', savedCount: 2, pendingCount: 2, version: 3, numberOffset: 4);
        $this->transaction($pb5, $testUser, 'starting_amount', 100);
        foreach (array_slice($savings5, 0, 2) as $s) {
            $this->transaction($pb5, $testUser, 'scheduled_add', 500, $s->saving_date);
        }

        // PB #6: Daily Expenses Jar (classic, active, EUR)
        $pb6 = PiggyBank::create([
            'user_id' => $testUser->id,
            'name' => 'Daily Expenses Jar',
            'type' => 'classic',
            'status' => 'active',
            'currency' => 'EUR',
            'chosen_strategy' => null,
            'selected_frequency' => null,
            'target_amount' => 0,
            'starting_amount' => 0,
            'price' => 0,
            'total_savings' => 0,
            'final_total' => 0,
            'remaining_amount' => 0,
        ]);

        $this->transaction($pb6, $testUser, 'manual_add', 50);
        $this->transaction($pb6, $testUser, 'manual_add', 30);
        $this->transaction($pb6, $testUser, 'manual_add', 20);
        $this->transaction($pb6, $testUser, 'manual_withdraw', -15);
        $this->transaction($pb6, $testUser, 'manual_withdraw', -25);

        // PB #7: Coin Collection (classic, done, USD)
        $pb7 = PiggyBank::create([
            'user_id' => $testUser->id,
            'name' => 'Coin Collection',
            'type' => 'classic',
            'status' => 'done',
            'currency' => 'USD',
            'chosen_strategy' => null,
            'selected_frequency' => null,
            'target_amount' => 0,
            'starting_amount' => 0,
            'price' => 0,
            'total_savings' => 0,
            'final_total' => 0,
            'remaining_amount' => 0,
            'actual_completed_at' => now()->subWeeks(4),
        ]);

        $this->transaction($pb7, $testUser, 'manual_add', 25);
        $this->transaction($pb7, $testUser, 'manual_add', 35);
        $this->transaction($pb7, $testUser, 'manual_add', 40);

        // PB #8: School Supplies (scheduled, active, XOF — zero-decimal currency)
        // 8 savings x 17500 = 140000 + 10000 starting = 150000 target
        $pb8 = PiggyBank::create([
            'user_id' => $testUser->id,
            'name' => 'School Supplies',
            'type' => 'scheduled',
            'status' => 'active',
            'currency' => 'XOF',
            'chosen_strategy' => 'enter-saving-amount',
            'selected_frequency' => 'months',
            'target_amount' => 150000,
            'starting_amount' => 10000,
            'price' => 150000,
            'total_savings' => 140000,
            'final_total' => 150000,
            'uptodate_final_total' => null,
            'remaining_amount' => 105000,
        ]);

        $savings8 = $this->createSchedule($pb8, 17500, 'months', savedCount: 2, pendingCount: 6);
        $this->transaction($pb8, $testUser, 'starting_amount', 10000);
        foreach (array_slice($savings8, 0, 2) as $s) {
            $this->transaction($pb8, $testUser, 'scheduled_add', 17500, $s->saving_date);
        }

        // ── Piggy Bank for admin@example.com ───────────────

        // PB #9: Side Project Fund (scheduled, active, USD, monthly)
        // 8 savings x 375 = 3000 target
        $pb9 = PiggyBank::create([
            'user_id' => $adminUser->id,
            'name' => 'Side Project Fund',
            'type' => 'scheduled',
            'status' => 'active',
            'currency' => 'USD',
            'chosen_strategy' => 'enter-saving-amount',
            'selected_frequency' => 'months',
            'target_amount' => 3000,
            'starting_amount' => 0,
            'price' => 3000,
            'total_savings' => 3000,
            'final_total' => 3000,
            'uptodate_final_total' => null,
            'remaining_amount' => 2250,
        ]);

        $savings9 = $this->createSchedule($pb9, 375, 'months', savedCount: 2, pendingCount: 6);
        foreach (array_slice($savings9, 0, 2) as $s) {
            $this->transaction($pb9, $adminUser, 'scheduled_add', 375, $s->saving_date);
        }

        // ── Vault ──────────────────────────────────────────

        $vault = Vault::create([
            'user_id' => $testUser->id,
            'name' => 'Savings Goals',
        ]);

        $pb1->update(['vault_id' => $vault->id]);
        $pb3->update(['vault_id' => $vault->id]);
    }

    /**
     * @return ScheduledSaving[]
     */
    private function createSchedule(
        PiggyBank $piggyBank,
        float $amount,
        string $unit,
        int $savedCount,
        int $pendingCount,
        int $version = 1,
        int $numberOffset = 0,
    ): array {
        $savings = [];
        $now = now();
        $number = $numberOffset;

        for ($i = $savedCount; $i >= 1; $i--) {
            $number++;
            $date = match ($unit) {
                'months' => $now->copy()->subMonths($i),
                default => $now->copy()->subWeeks($i),
            };
            $savings[] = ScheduledSaving::create([
                'piggy_bank_id' => $piggyBank->id,
                'saving_number' => $number,
                'amount' => $amount,
                'status' => 'saved',
                'saved_amount' => $amount,
                'saving_date' => $date,
                'archived' => false,
                'recalculation_version' => $version,
                'last_modified_at' => $date,
            ]);
        }

        for ($i = 1; $i <= $pendingCount; $i++) {
            $number++;
            $date = match ($unit) {
                'months' => $now->copy()->addMonths($i),
                default => $now->copy()->addWeeks($i),
            };
            $savings[] = ScheduledSaving::create([
                'piggy_bank_id' => $piggyBank->id,
                'saving_number' => $number,
                'amount' => $amount,
                'status' => 'pending',
                'saving_date' => $date,
                'archived' => false,
                'recalculation_version' => $version,
            ]);
        }

        return $savings;
    }

    private function transaction(
        PiggyBank $piggyBank,
        User $user,
        string $type,
        float $amount,
        mixed $scheduledFor = null,
    ): void {
        PiggyBankTransaction::create([
            'piggy_bank_id' => $piggyBank->id,
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $amount,
            'scheduled_for' => $scheduledFor,
        ]);
    }
}
