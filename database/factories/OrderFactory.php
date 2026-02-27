<?php

namespace Database\Factories;

use App\Enums\DayType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomElement([35000, 60000, 95000, 120000, 145000]);

        return [
            'user_id' => User::factory(),
            'code' => Order::generateCode(),
            'visit_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'day_type' => fake()->randomElement(DayType::cases()),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'status' => OrderStatus::Pending,
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Confirmed,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
        ]);
    }

    public function midtrans(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::Midtrans,
        ]);
    }

    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::BankTransfer,
        ]);
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::Cash,
        ]);
    }
}
