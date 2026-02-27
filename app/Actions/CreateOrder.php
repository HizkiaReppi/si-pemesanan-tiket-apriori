<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\OrderCreated;
use App\Models\Addon;
use App\Models\Order;
use App\Models\TicketType;
use App\Services\PricingService;
use Illuminate\Support\Facades\DB;

class CreateOrder
{
    public function __construct(private PricingService $pricingService) {}

    /**
     * Create a new order from cart data.
     *
     * @param  array{visit_date: string, items: array<int, array{type: string, id: int, name: string, price: float, quantity: int}>}  $cart
     * @param  array{name: string, email: string, phone: string}  $customer
     */
    public function execute(
        array $cart,
        array $customer,
        PaymentMethod $paymentMethod,
        ?int $userId = null,
        float $discount = 0,
    ): Order {
        return DB::transaction(function () use ($cart, $customer, $paymentMethod, $userId, $discount) {
            $visitDate = $cart['visit_date'];
            $dayType = $this->pricingService->getDayType($visitDate);
            $subtotal = $this->pricingService->calculateSubtotal($cart['items'], $visitDate);
            $total = max(0, $subtotal - $discount);

            $order = Order::create([
                'user_id' => $userId,
                'visit_date' => $visitDate,
                'day_type' => $dayType,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => OrderStatus::Pending,
                'payment_method' => $paymentMethod,
                'expires_at' => now()->addHours(2),
            ]);

            foreach ($cart['items'] as $item) {
                $itemable = $item['type'] === 'ticket'
                    ? TicketType::find($item['id'])
                    : Addon::find($item['id']);

                if (! $itemable) {
                    continue;
                }

                $order->items()->create([
                    'itemable_type' => $itemable::class,
                    'itemable_id' => $itemable->id,
                    'item_name' => $item['name'],
                    'item_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            OrderCreated::dispatch($order);

            return $order->load('items');
        });
    }
}
