<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'name' => fake()->words(3, true),
            'discount_type' => fake()->randomElement(['percentage', 'fixed']),
            'discount_value' => fake()->randomElement([10, 15, 20, 5000, 10000]),
            'min_order' => fake()->optional()->randomElement([50000, 100000]),
            'max_discount' => fake()->optional()->randomElement([20000, 50000]),
            'usage_limit' => fake()->optional()->numberBetween(10, 100),
            'used_count' => 0,
            'valid_from' => now(),
            'valid_until' => now()->addMonth(),
            'is_active' => true,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => now()->subMonths(2),
            'valid_until' => now()->subMonth(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function percentage(float $value = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'percentage',
            'discount_value' => $value,
        ]);
    }

    public function fixed(float $value = 10000): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed',
            'discount_value' => $value,
        ]);
    }
}
