<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPaid;
use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "updated" event.
     * When payment status changes to Paid, update order status and fire event.
     */
    public function updated(Payment $payment): void
    {
        if (! $payment->isDirty('status')) {
            return;
        }

        if ($payment->status === PaymentStatus::Paid) {
            $order = $payment->order;

            if (! $order->isPaid()) {
                $order->update([
                    'status' => OrderStatus::Paid,
                ]);

                OrderPaid::dispatch($order->fresh());
            }
        }
    }
}
