<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/** F10 xlsx: seluruh transaksi (tanpa paginasi) dari BukuRealisasiQuery::semua(). */
class BukuRealisasiExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /** @param array<string, mixed> $hasil */
    public function __construct(private readonly array $hasil) {}

    public function title(): string
    {
        return 'Buku Realisasi';
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Kode', 'Sub Kegiatan', 'Bidang', 'Uraian', 'No. SP2D', 'Jumlah', 'Lampiran', 'Dicatat oleh'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->hasil['baris'] as $b) {
            $rows[] = [$b['no'], $b['tanggal'], $b['kode'], $b['nama'], $b['bidang'], $b['uraian'], $b['no_sp2d'], $b['jumlah'], $b['lampiran'] ? 'Ada' : '-', $b['dicatat_oleh']];
        }
        if ($rows === []) {
            return [['Tidak ada data']];
        }
        $rows[] = ['', '', '', 'TOTAL', '', '', '', $this->hasil['total'], '', ''];

        return $rows;
    }
}
