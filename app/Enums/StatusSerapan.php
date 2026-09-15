<?php

namespace App\Enums;

/** Ambang docs/04: ≥100 Habis, ≥90 Kritis, ≥60 Sedang, <60 Aman. Label tunggal "Kritis" (landmine #2). */
enum StatusSerapan: string
{
    case Aman = 'aman';
    case Sedang = 'sedang';
    case Kritis = 'kritis';
    case Habis = 'habis';

    public static function dariPersen(int|float $serapan): self
    {
        return match (true) {
            $serapan >= 100 => self::Habis,
            $serapan >= 90 => self::Kritis,
            $serapan >= 60 => self::Sedang,
            default => self::Aman,
        };
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Warna badge docs/26. */
    public function warnaBadge(): string
    {
        return match ($this) {
            self::Aman => 'green',
            self::Sedang => 'yellow',
            self::Kritis, self::Habis => 'red',
        };
    }
}
