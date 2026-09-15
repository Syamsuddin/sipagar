<?php

namespace App\Enums;

enum StatusTahun: string
{
    case Draft = 'draft';
    case Aktif = 'aktif';
    case Terkunci = 'terkunci';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Aktif => 'Aktif',
            self::Terkunci => 'Terkunci',
        };
    }

    /** Warna badge docs/26. */
    public function warnaBadge(): string
    {
        return match ($this) {
            self::Draft => 'yellow',
            self::Aktif => 'green',
            self::Terkunci => 'red',
        };
    }
}
