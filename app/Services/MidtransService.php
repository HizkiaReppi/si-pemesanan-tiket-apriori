<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create a Snap payment token for an order.
     *
     * @return array{token: string, redirect_url: string}
     */
    public function createSnapToken(Order $order): array
    {
        $order->load('items');

        $itemDetails = $order->items->map(fn ($item) => [
            'id' => $item->itemable_type === 'App\\Models\\TicketType' ? "ticket-{$item->itemable_id}" : "addon-{$item->itemable_id}",
            'price' => (int) $item->item_price,
            'quantity' => $item->quantity,
            'name' => substr($item->item_name, 0, 50),
        ])->toArray();

        if ($order->discount > 0) {
            $itemDetails[] = [
                'id' => 'discount',
                'price' => (int) -$order->discount,
                'quantity' => 1,
                'name' => 'Diskon Voucher',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->code,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
            ],
            'callbacks' => [
                'finish' => route('pesanan.detail', $order),
            ],
            'expiry' => [
                'start_time' => $order->created_at->format('Y-m-d H:i:s O'),
                'unit' => 'hours',
                'duration' => 2,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        $baseUrl = config('services.midtrans.is_production', false)
            ? 'https://app.midtrans.com/snap/v2/vtweb/'
            : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

        return [
            'token' => $snapToken,
            'redirect_url' => $baseUrl.$snapToken,
        ];
    }

    /**
     * Get the status of a transaction from Midtrans.
     *
     * @return array<string, mixed>
     */
    public function getTransactionStatus(string $orderId): array
    {
        return (array) Transaction::status($orderId);
    }

    /**
     * Verify the signature of a Midtrans notification.
     */
    public function verifyNotificationSignature(array $notification): bool
    {
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $serverKey = config('services.midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return ($notification['signature_key'] ?? '') === $expectedSignature;
    }

    /**
     * Determine if a transaction is successful based on Midtrans notification.
     */
    public function isTransactionSuccess(array $notification): bool
    {
        $transactionStatus = $notification['transaction_status'] ?? '';
        $fraudStatus = $notification['fraud_status'] ?? 'accept';

        return in_array($transactionStatus, ['capture', 'settlement'])
            && $fraudStatus === 'accept';
    }

    /**
     * Determine if a transaction has expired or been denied.
     */
    public function isTransactionFailed(array $notification): bool
    {
        $transactionStatus = $notification['transaction_status'] ?? '';

        return in_array($transactionStatus, ['deny', 'cancel', 'expire']);
    }

    /**
     * Determine if a transaction is pending.
     */
    public function isTransactionPending(array $notification): bool
    {
        return ($notification['transaction_status'] ?? '') === 'pending';
    }

    /**
     * Get the Midtrans client key for frontend usage.
     */
    public function getClientKey(): string
    {
        return config('services.midtrans.client_key', '');
    }

    /**
     * Get the Snap JS URL based on environment.
     */
    public function getSnapJsUrl(): string
    {
        return config('services.midtrans.is_production', false)
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }
}
