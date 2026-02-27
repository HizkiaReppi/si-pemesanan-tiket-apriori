<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __construct(private MidtransService $midtransService) {}

    /**
     * Handle incoming Midtrans payment notification.
     */
    public function handle(Request $request): JsonResponse
    {
        $notification = $request->all();

        if (! $this->midtransService->verifyNotificationSignature($notification)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderCode = $notification['order_id'] ?? '';
        $order = Order::where('code', $orderCode)->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payment = $order->payment;

        if (! $payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($this->midtransService->isTransactionSuccess($notification)) {
            $payment->update([
                'status' => PaymentStatus::Paid,
                'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                'paid_at' => now(),
                'metadata' => $notification,
            ]);
        } elseif ($this->midtransService->isTransactionFailed($notification)) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                'metadata' => $notification,
            ]);

            if ($order->status === OrderStatus::Pending || $order->status === OrderStatus::AwaitingPayment) {
                $order->update(['status' => OrderStatus::Expired]);
            }
        } elseif ($this->midtransService->isTransactionPending($notification)) {
            $payment->update([
                'status' => PaymentStatus::Pending,
                'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                'metadata' => $notification,
            ]);

            if ($order->status === OrderStatus::Pending) {
                $order->update(['status' => OrderStatus::AwaitingPayment]);
            }
        }

        return response()->json(['message' => 'OK']);
    }
}
