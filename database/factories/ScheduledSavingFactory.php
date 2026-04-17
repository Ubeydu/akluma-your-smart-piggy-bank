<?php

namespace Database\Factories;

use App\Models\PiggyBank;
use App\Models\ScheduledSaving;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduledSaving>
 */
class ScheduledSavingFactory extends Factory
{
    protected $model = ScheduledSaving::class;

    public function definition(): array
    {
        return [
            'piggy_bank_id' => PiggyBank::factory(),
            'saving_number' => $this->faker->numberBetween(1, 52),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => 'pending',
            'saving_date' => $this->faker->dateTimeBetween('-3 months', '+12 months'),
            'archived' => false,
            'recalculation_version' => 1,
        ];
    }

    public function saved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'saved',
            'saved_amount' => $attributes['amount'],
            'last_modified_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived' => true,
        ]);
    }
}
