<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomElement([25000, 35000, 50000]);
        $quantity = fake()->numberBetween(1, 5);

        return [
            'order_id' => Order::factory(),
            'itemable_type' => TicketType::class,
            'itemable_id' => TicketType::factory(),
            'item_name' => 'Tiket Dewasa',
            'item_price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
        ];
    }
}
