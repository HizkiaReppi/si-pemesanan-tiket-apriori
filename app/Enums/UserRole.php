<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case Cashier = 'cashier';
    case GateStaff = 'gate_staff';
    case Owner = 'owner';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Pelanggan',
            self::Admin => 'Administrator',
            self::Cashier => 'Kasir',
            self::GateStaff => 'Petugas Gerbang',
            self::Owner => 'Pemilik',
        };
    }
}
