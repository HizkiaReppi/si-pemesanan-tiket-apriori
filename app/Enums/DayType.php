<?php

namespace App\Enums;

enum DayType: string
{
    case Weekday = 'weekday';
    case Weekend = 'weekend';

    public function label(): string
    {
        return match ($this) {
            self::Weekday => 'Hari Kerja',
            self::Weekend => 'Akhir Pekan',
        };
    }
}
