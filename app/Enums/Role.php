<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Operator = 'operator';
    case Pimpinan = 'pimpinan';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Operator => 'Operator',
            self::Pimpinan => 'Pimpinan',
        };
    }

    /** @return array<string, string> value => label */
    public static function pilihan(): array
    {
        return array_column(array_map(fn (self $r) => [$r->value, $r->label()], self::cases()), 1, 0);
    }
}
