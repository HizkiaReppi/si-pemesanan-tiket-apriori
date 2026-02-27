<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Events\PaymentVerified;
use App\Events\TicketRedeemed;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;

class LogAuditTrail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            OrderCreated::class => 'handleOrderCreated',
            OrderPaid::class => 'handleOrderPaid',
            OrderCompleted::class => 'handleOrderCompleted',
            PaymentVerified::class => 'handlePaymentVerified',
            TicketRedeemed::class => 'handleTicketRedeemed',
        ];
    }

    public function handleOrderCreated(OrderCreated $event): void
    {
        AuditLog::log(
            action: 'order_created',
            auditable: $event->order,
            newValues: [
                'code' => $event->order->code,
                'total' => $event->order->total,
                'visit_date' => $event->order->visit_date->toDateString(),
            ],
            userId: $event->order->user_id,
        );
    }

    public function handleOrderPaid(OrderPaid $event): void
    {
        AuditLog::log(
            action: 'order_paid',
            auditable: $event->order,
            newValues: [
                'code' => $event->order->code,
                'payment_method' => $event->order->payment_method?->value,
                'total' => $event->order->total,
            ],
            userId: $event->order->user_id,
        );
    }

    public function handlePaymentVerified(PaymentVerified $event): void
    {
        AuditLog::log(
            action: 'payment_verified',
            auditable: $event->payment,
            newValues: [
                'order_code' => $event->payment->order->code,
                'amount' => $event->payment->amount,
                'method' => $event->payment->method->value,
            ],
            userId: $event->payment->verified_by,
        );
    }

    public function handleTicketRedeemed(TicketRedeemed $event): void
    {
        AuditLog::log(
            action: 'ticket_redeemed',
            auditable: $event->checkIn,
            newValues: [
                'order_code' => $event->checkIn->order->code,
                'status' => $event->checkIn->status->value,
            ],
            userId: $event->checkIn->scanned_by,
        );
    }

    public function handleOrderCompleted(OrderCompleted $event): void
    {
        AuditLog::log(
            action: 'order_completed',
            auditable: $event->order,
            newValues: [
                'code' => $event->order->code,
                'visit_date' => $event->order->visit_date->toDateString(),
            ],
            userId: $event->order->user_id,
        );
    }
}
