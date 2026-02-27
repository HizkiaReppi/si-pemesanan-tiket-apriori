<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\AuditLog;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelOrder
{
    /**
     * Cancel an existing order.
     */
    public function execute(Order $order, ?int $cancelledBy = null, ?string $reason = null): Order
    {
        if (in_array($order->status, [OrderStatus::Completed, OrderStatus::Cancelled])) {
            throw ValidationException::withMessages([
                'order' => 'Pesanan ini tidak dapat dibatalkan.',
            ]);
        }

        return DB::transaction(function () use ($order, $cancelledBy, $reason) {
            $oldStatus = $order->status;

            $order->update([
                'status' => OrderStatus::Cancelled,
                'notes' => $reason,
            ]);

            // Restore voucher usage if any
            foreach ($order->voucherUsages as $usage) {
                $usage->voucher->decrement('used_count');
            }

            AuditLog::log(
                action: 'order_cancelled',
                auditable: $order,
                oldValues: ['status' => $oldStatus->value],
                newValues: [
                    'status' => OrderStatus::Cancelled->value,
                    'reason' => $reason,
                ],
                userId: $cancelledBy,
            );

            return $order->fresh();
        });
    }
}
