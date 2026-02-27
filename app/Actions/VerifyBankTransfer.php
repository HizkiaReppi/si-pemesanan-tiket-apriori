<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPaid;
use App\Events\PaymentVerified;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerifyBankTransfer
{
    /**
     * Verify a bank transfer payment.
     */
    public function execute(Payment $payment, int $verifiedBy, bool $approved = true): Payment
    {
        if ($payment->status !== PaymentStatus::Verifying) {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran ini tidak dalam status menunggu verifikasi.',
            ]);
        }

        return DB::transaction(function () use ($payment, $verifiedBy, $approved) {
            if ($approved) {
                $payment->update([
                    'status' => PaymentStatus::Paid,
                    'verified_by' => $verifiedBy,
                    'verified_at' => now(),
                    'paid_at' => now(),
                ]);

                $payment->order->update([
                    'status' => OrderStatus::Paid,
                ]);

                PaymentVerified::dispatch($payment);
                OrderPaid::dispatch($payment->order);
            } else {
                $payment->update([
                    'status' => PaymentStatus::Failed,
                    'verified_by' => $verifiedBy,
                    'verified_at' => now(),
                ]);

                $payment->order->update([
                    'status' => OrderStatus::Cancelled,
                ]);
            }

            return $payment->fresh(['order']);
        });
    }
}
