<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'status' => PaymentStatus::Pending,
            'amount' => fake()->randomElement([35000, 60000, 95000, 120000, 145000]),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function verifying(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Verifying,
            'method' => PaymentMethod::BankTransfer,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Failed,
        ]);
    }
}
