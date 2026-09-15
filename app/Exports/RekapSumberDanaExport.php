<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/** F09 sheet per Sumber Dana; dari RekapQuery. */
class RekapSumberDanaExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /** @param array<string, mixed> $hasil keluaran RekapQuery::jalankan() */
    public function __construct(private readonly array $hasil) {}

    public function title(): string
    {
        return 'Per Sumber Dana';
    }

    public function headings(): array
    {
        return ['No', 'Kode', 'Sumber Dana', 'Jumlah Sub Kegiatan', 'Pagu', 'Realisasi', 'Sisa', 'Serapan (%)', 'Status'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->hasil['sumber_dana'] as $r) {
            $rows[] = [$r['no'], $r['kode'], $r['nama'], $r['jumlah_sub'], $r['pagu'], $r['realisasi'], $r['sisa'], $r['serapan'], $r['status']->label()];
        }
        if ($rows === []) {
            return [['Tidak ada data']];
        }
        $t = $this->hasil['total'];
        $rows[] = ['', '', 'TOTAL', $t['jumlah_sub'], $t['pagu'], $t['realisasi'], $t['sisa'], $t['serapan'], $t['status']->label()];

        return $rows;
    }
}
