<?php

namespace Database\Factories;

use App\Models\PiggyBank;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PiggyBank>
 */
class PiggyBankFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PiggyBank::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 50, 5000),
            'link' => $this->faker->optional(0.3)->url(),
            'details' => $this->faker->optional(0.3)->paragraphs(2, true),
            'starting_amount' => $this->faker->randomFloat(2, 0, 1000),
            'image' => 'images/piggy_banks/default_piggy_bank.png',
            'currency' => 'TRY',
            'balance' => fn (array $attributes) => $attributes['starting_amount'],
            'date' => $this->faker->date('Y-m-d', '+1 year'),
            'status' => $this->faker->randomElement(['active', 'paused', 'done', 'cancelled']),
        ];
    }

    /**
     * Indicate that the piggy bank is active.
     *
     * @return $this
     */
    public function active()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the piggy bank is paused.
     *
     * @return $this
     */
    public function paused()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
        ]);
    }

    /**
     * Indicate that the piggy bank is done.
     *
     * @return $this
     */
    public function done()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
        ]);
    }

    /**
     * Indicate that the piggy bank is cancelled.
     *
     * @return $this
     */
    public function cancelled()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
