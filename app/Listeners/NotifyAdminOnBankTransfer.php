<?php

namespace App\Listeners;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Events\OrderCreated;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyAdminOnBankTransfer implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if ($order->payment_method !== PaymentMethod::BankTransfer) {
            return;
        }

        $admins = User::where('role', UserRole::Admin)->get();

        foreach ($admins as $admin) {
            Log::info('Bank transfer order pending verification', [
                'order_code' => $order->code,
                'admin_email' => $admin->email,
                'total' => $order->total,
            ]);

            // TODO: Send notification via Mail or database notification
            // $admin->notify(new BankTransferPendingNotification($order));
        }
    }
}
