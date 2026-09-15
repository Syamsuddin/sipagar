<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** F09 xlsx: dua sheet (Sumber Dana, Bidang) dari satu hasil RekapQuery. */
class RekapExport implements WithMultipleSheets
{
    use Exportable;

    /** @param array<string, mixed> $hasil */
    public function __construct(private readonly array $hasil) {}

    public function sheets(): array
    {
        return [new RekapSumberDanaExport($this->hasil), new RekapBidangExport($this->hasil)];
    }
}
