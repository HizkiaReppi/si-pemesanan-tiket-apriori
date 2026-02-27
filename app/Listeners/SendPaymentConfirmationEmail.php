<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\ETicketMail;
use App\Mail\PaymentConfirmedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order->load('items');

        if (! $order->customer_email) {
            return;
        }

        // Send payment confirmation
        Mail::to($order->customer_email)->send(new PaymentConfirmedMail($order));

        // Send e-ticket if QR token exists
        if ($order->qr_token) {
            Mail::to($order->customer_email)
                ->later(now()->addSeconds(10), new ETicketMail($order));
        }
    }
}
