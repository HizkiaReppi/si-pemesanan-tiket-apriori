<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderCreatedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load('items');

        // Send confirmation to customer
        if ($order->customer_email) {
            Mail::to($order->customer_email)->send(new OrderCreatedMail($order));
        }

        // Send notification to admins
        $admins = User::where('role', \App\Enums\UserRole::Admin)->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new AdminNewOrderMail($order));
        }
    }
}
