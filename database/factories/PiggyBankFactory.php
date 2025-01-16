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
            'target_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'chosen_strategy' => $this->faker->randomElement(['weekly', 'monthly']), // adjust these values based on your actual strategies
            'selected_frequency' => $this->faker->randomElement(['weekly', 'bi-weekly']), // adjust these values based on your actual frequencies
            'starting_amount' => $this->faker->randomFloat(2, 0, 1000),
            'current_balance' => fn (array $attributes) => $attributes['starting_amount'],
            'total_savings' => $this->faker->randomFloat(2, 1000, 10000),
            'extra_savings' => $this->faker->optional(0.3)->randomFloat(2, 0, 500),
            'link' => $this->faker->optional(0.3)->url(),
            'details' => $this->faker->optional(0.3)->paragraphs(2, true),
            'preview_image' => 'images/piggy_banks/default_piggy_bank.png',
            'currency' => $this->faker->randomElement(array_keys(config('app.currencies'))),
            'status' => $this->faker->randomElement(['active', 'paused', 'done', 'cancelled']),
            'preview_title' => $this->faker->optional()->sentence,
            'preview_description' => $this->faker->optional()->paragraph,
            'preview_url' => $this->faker->optional()->url,
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
