<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/** F11 xlsx: tabel 12 bulan dari TrenSerapanQuery. */
class TrenSerapanExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /** @param array<string, mixed> $hasil */
    public function __construct(private readonly array $hasil) {}

    public function title(): string
    {
        return 'Tren '.$this->hasil['filter']->tahun->tahun;
    }

    public function headings(): array
    {
        $lalu = $this->hasil['tahun_lalu'] ?? 'Tahun lalu';

        return ['Bulan', 'Realisasi Kumulatif (Rp)', 'Realisasi (%)', 'Target Kumulatif (Rp)', 'Target (%)', 'Deviasi (%)', "Realisasi {$lalu} (Rp)", "Realisasi {$lalu} (%)"];
    }

    public function array(): array
    {
        if ($this->hasil['pagu'] === 0) {
            return [['Tidak ada data']];
        }
        $rows = [];
        foreach ($this->hasil['baris'] as $b) {
            $rows[] = [$b['bulan'], $b['realisasi'], $b['realisasi_persen'], $b['target'], $b['target_persen'], $b['deviasi'], $b['realisasi_lalu'], $b['realisasi_lalu_persen']];
        }

        return $rows;
    }
}
