<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplyVoucher
{
    /**
     * Apply a voucher code to calculate discount.
     *
     * @return array{voucher: Voucher, discount: float}
     */
    public function calculate(string $code, float $subtotal): array
    {
        $voucher = Voucher::where('code', strtoupper($code))->first();

        if (! $voucher) {
            throw ValidationException::withMessages([
                'voucher' => 'Kode voucher tidak ditemukan.',
            ]);
        }

        if (! $voucher->isValid()) {
            throw ValidationException::withMessages([
                'voucher' => 'Kode voucher sudah tidak berlaku.',
            ]);
        }

        $discount = $voucher->calculateDiscount($subtotal);

        if ($discount <= 0) {
            throw ValidationException::withMessages([
                'voucher' => 'Minimum pembelian belum terpenuhi.',
            ]);
        }

        return [
            'voucher' => $voucher,
            'discount' => $discount,
        ];
    }

    /**
     * Record voucher usage for an order.
     */
    public function record(Voucher $voucher, Order $order, float $discountAmount): VoucherUsage
    {
        return DB::transaction(function () use ($voucher, $order, $discountAmount) {
            $voucher->increment('used_count');

            return VoucherUsage::create([
                'voucher_id' => $voucher->id,
                'order_id' => $order->id,
                'discount_amount' => $discountAmount,
            ]);
        });
    }
}
