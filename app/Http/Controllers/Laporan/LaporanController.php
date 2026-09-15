<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\Setting;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Queries\LaporanFilter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Dasar 4 laporan: data filter bersama, unduh xlsx/pdf dengan nama `sipagar_<jenis>_<tahun>_<periode>` (P4 docs/06).
 * PDF: layouts/pdf + print.css, kop & ttd dari settings (docs/26 §PDF).
 */
abstract class LaporanController extends Controller
{
    /** @return array<string, mixed> */
    protected function dataFilter(?LaporanFilter $f): array
    {
        return [
            'filter' => $f,
            'daftarTahun' => TahunAnggaran::orderByDesc('tahun')->get(),
            'daftarBidang' => Bidang::where('is_active', true)->orderBy('urutan')->get(),
            'daftarSumber' => SumberDana::where('is_active', true)->orderBy('urutan')->get(),
        ];
    }

    protected function namaBerkas(string $jenis, LaporanFilter $f, string $periode, string $ext): string
    {
        return "sipagar_{$jenis}_{$f->tahun->tahun}_{$periode}.{$ext}";
    }

    protected function unduhXlsx(object $export, string $nama): BinaryFileResponse
    {
        return Excel::download($export, $nama);
    }

    /** @param array<string, mixed> $data */
    protected function unduhPdf(string $view, array $data, string $nama, string $orientasi = 'portrait'): Response
    {
        $pdf = Pdf::loadView($view, $data + [
            'pengaturan' => Setting::semua(),
            'printCss' => file_get_contents(resource_path('css/print.css')),
            'dicetak' => now(),
        ])->setPaper('a4', $orientasi);

        return $pdf->download($nama);
    }
}
