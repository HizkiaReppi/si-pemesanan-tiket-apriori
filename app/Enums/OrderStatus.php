<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::AwaitingPayment => 'Menunggu Pembayaran',
            self::Paid => 'Dibayar',
            self::Confirmed => 'Dikonfirmasi',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Expired => 'Kedaluwarsa',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::AwaitingPayment => 'orange',
            self::Paid => 'blue',
            self::Confirmed => 'green',
            self::Completed => 'emerald',
            self::Cancelled => 'red',
            self::Expired => 'gray',
        };
    }
}
