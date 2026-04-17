<?php

namespace Database\Factories;

use App\Models\PiggyBank;
use App\Models\PiggyBankTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PiggyBankTransaction>
 */
class PiggyBankTransactionFactory extends Factory
{
    protected $model = PiggyBankTransaction::class;

    public function definition(): array
    {
        return [
            'piggy_bank_id' => PiggyBank::factory(),
            'user_id' => User::factory(),
            'type' => 'manual_add',
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'note' => null,
            'scheduled_for' => null,
        ];
    }

    public function startingAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'starting_amount',
        ]);
    }

    public function scheduledAdd(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'scheduled_add',
        ]);
    }

    public function manualAdd(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'manual_add',
        ]);
    }

    public function manualWithdraw(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'manual_withdraw',
            'amount' => -abs($attributes['amount']),
        ]);
    }
}
