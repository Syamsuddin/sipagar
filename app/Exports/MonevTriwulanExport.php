<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/** F08 xlsx: angka int (docs/16), subtotal per Program & total, dari MonevTriwulanQuery. */
class MonevTriwulanExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /** @param array<string, mixed> $hasil keluaran MonevTriwulanQuery::jalankan() */
    public function __construct(private readonly array $hasil) {}

    public function title(): string
    {
        return 'Monev '.$this->hasil['filter']->periodeTw();
    }

    public function headings(): array
    {
        return ['No', 'Kode', 'Sub Kegiatan', 'Bidang', 'Sumber Dana', 'Pagu', 'Target Keu (Rp)', 'Target Keu (%)', 'Realisasi Keu (Rp)', 'Realisasi Keu (%)', 'Deviasi Keu (%)', 'Target Fisik (%)', 'Realisasi Fisik (%)', 'Deviasi Fisik', 'Status'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->hasil['kelompok'] as $g) {
            $rows[] = ['', $g['program']['kode'], $g['program']['nama'], '', '', '', '', '', '', '', '', '', '', '', ''];
            foreach ($g['baris'] as $b) {
                $rows[] = [$b['no'], $b['kode'], $b['nama'], $b['bidang'], $b['sumber'], $b['pagu'], $b['target_keu'], $b['target_keu_persen'], $b['realisasi'], $b['realisasi_persen'], $b['deviasi_keu'], $b['target_fisik'], $b['realisasi_fisik'], $b['deviasi_fisik'], $b['status']->label()];
            }
            $s = $g['subtotal'];
            $rows[] = ['', '', 'Subtotal '.$g['program']['kode'], '', '', $s['pagu'], $s['target_keu'], $s['target_keu_persen'], $s['realisasi'], $s['realisasi_persen'], $s['deviasi_keu'], '', '', '', $s['status']->label()];
        }
        if ($rows === []) {
            return [['Tidak ada data']];
        }
        $t = $this->hasil['total'];
        $rows[] = ['', '', 'TOTAL', '', '', $t['pagu'], $t['target_keu'], $t['target_keu_persen'], $t['realisasi'], $t['realisasi_persen'], $t['deviasi_keu'], '', '', '', $t['status']->label()];

        return $rows;
    }
}
