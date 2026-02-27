<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Verifying = 'verifying';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Paid => 'Dibayar',
            self::Failed => 'Gagal',
            self::Expired => 'Kedaluwarsa',
            self::Verifying => 'Menunggu Verifikasi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Paid => 'green',
            self::Failed => 'red',
            self::Expired => 'gray',
            self::Verifying => 'blue',
        };
    }
}
