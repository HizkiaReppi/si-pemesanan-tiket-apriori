<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Midtrans = 'midtrans';
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::Midtrans => 'Pembayaran Instan (QRIS, VA, e-Wallet)',
            self::BankTransfer => 'Transfer Bank Manual',
            self::Cash => 'Tunai',
        };
    }
}
