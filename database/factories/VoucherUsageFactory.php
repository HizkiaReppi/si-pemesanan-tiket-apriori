<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VoucherUsage>
 */
class VoucherUsageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'voucher_id' => Voucher::factory(),
            'order_id' => Order::factory(),
            'discount_amount' => fake()->randomElement([5000, 10000, 15000, 20000]),
        ];
    }
}
