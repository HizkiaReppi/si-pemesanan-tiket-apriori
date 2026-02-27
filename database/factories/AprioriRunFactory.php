<?php

namespace Database\Factories;

use App\Models\AprioriRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AprioriRun>
 */
class AprioriRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'min_support' => 0.1,
            'min_confidence' => 0.5,
            'date_from' => now()->subMonth(),
            'date_to' => now(),
            'total_transactions' => 0,
            'total_items' => 0,
            'status' => 'pending',
            'triggered_by' => User::factory()->admin(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
            'total_transactions' => fake()->numberBetween(50, 500),
            'total_items' => fake()->numberBetween(5, 15),
        ]);
    }

    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'started_at' => now()->subMinutes(2),
            'completed_at' => now(),
            'error_message' => 'Not enough transactions to analyze.',
        ]);
    }
}
