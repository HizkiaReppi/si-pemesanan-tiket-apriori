<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Services\QrCodeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateETicketOnPayment implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(public QrCodeService $qrCodeService) {}

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        if ($order->qr_token) {
            return;
        }

        $token = $this->qrCodeService->generateToken(
            $order->code,
            $order->visit_date->toDateString(),
        );

        $order->update(['qr_token' => $token]);
    }
}
