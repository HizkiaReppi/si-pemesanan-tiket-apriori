<?php

namespace Database\Factories;

use App\Enums\CheckInStatus;
use App\Models\CheckIn;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckIn>
 */
class CheckInFactory extends Factory
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
            'scanned_by' => User::factory()->gateStaff(),
            'status' => CheckInStatus::Valid,
            'scanned_at' => now(),
            'device_info' => 'Tablet Gate 1',
        ];
    }

    public function invalid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CheckInStatus::Invalid,
        ]);
    }

    public function alreadyUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CheckInStatus::AlreadyUsed,
        ]);
    }
}
