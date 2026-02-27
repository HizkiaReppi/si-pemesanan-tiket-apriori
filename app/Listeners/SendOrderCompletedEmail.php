<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Mail\OrderCompletedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderCompletedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderCompleted $event): void
    {
        $order = $event->order->load('items');

        if (! $order->customer_email) {
            return;
        }

        Mail::to($order->customer_email)->send(new OrderCompletedMail($order));
    }
}
