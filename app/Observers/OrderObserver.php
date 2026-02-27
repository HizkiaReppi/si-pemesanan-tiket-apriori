<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * Auto-create a payment record for the order.
     */
    public function created(Order $order): void
    {
        $order->payment()->create([
            'method' => $order->payment_method,
            'status' => PaymentStatus::Pending,
            'amount' => $order->total,
        ]);
    }

    /**
     * Handle the Order "updating" event.
     * Manage status transition side effects.
     */
    public function updating(Order $order): void
    {
        if (! $order->isDirty('status')) {
            return;
        }

        $newStatus = $order->status;

        if ($newStatus === OrderStatus::Cancelled || $newStatus === OrderStatus::Expired) {
            $order->payment?->update([
                'status' => $newStatus === OrderStatus::Expired
                    ? PaymentStatus::Expired
                    : PaymentStatus::Failed,
            ]);
        }
    }
}
