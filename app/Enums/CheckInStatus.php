<?php

namespace App\Enums;

enum CheckInStatus: string
{
    case Valid = 'valid';
    case Invalid = 'invalid';
    case AlreadyUsed = 'already_used';
    case Expired = 'expired';
    case WrongDate = 'wrong_date';

    public function label(): string
    {
        return match ($this) {
            self::Valid => 'Valid',
            self::Invalid => 'Tidak Valid',
            self::AlreadyUsed => 'Sudah Digunakan',
            self::Expired => 'Kedaluwarsa',
            self::WrongDate => 'Tanggal Tidak Sesuai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Valid => 'green',
            self::Invalid => 'red',
            self::AlreadyUsed => 'orange',
            self::Expired => 'gray',
            self::WrongDate => 'yellow',
        };
    }
}
