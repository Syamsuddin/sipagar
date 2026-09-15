<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * S0: data dummy statis untuk verifikasi visual terhadap prototipe.
     * S3 (F07) menggantinya dengan DashboardQuery + SerapanCalculator.
     */
    public function index(): View
    {
        $ringkasan = [
            ['nama' => 'Pelatihan Dasar CPNS', 'tahun' => 2025, 'sumber' => 'apbd', 'sumber_label' => 'APBD', 'pagu' => 250_000_000, 'realisasi' => 180_000_000, 'serapan' => 72.0, 'status' => 'green', 'status_label' => 'Normal'],
            ['nama' => 'Pengadaan ASN', 'tahun' => 2025, 'sumber' => 'apbn', 'sumber_label' => 'APBN', 'pagu' => 120_000_000, 'realisasi' => 112_000_000, 'serapan' => 93.33, 'status' => 'red', 'status_label' => 'Kritis'],
            ['nama' => 'Tunjangan Kinerja (TPP)', 'tahun' => 2025, 'sumber' => 'tpp', 'sumber_label' => 'TPP', 'pagu' => 2_400_000_000, 'realisasi' => 1_800_000_000, 'serapan' => 75.0, 'status' => 'yellow', 'status_label' => 'Waspada'],
        ];

        $totalPagu = array_sum(array_column($ringkasan, 'pagu'));
        $totalRealisasi = array_sum(array_column($ringkasan, 'realisasi'));

        return view('dashboard.index', [
            'ringkasan' => $ringkasan,
            'totalPagu' => $totalPagu,
            'totalRealisasi' => $totalRealisasi,
            'sisa' => $totalPagu - $totalRealisasi,
            'serapan' => $totalPagu > 0 ? round($totalRealisasi / $totalPagu * 100, 2) : 0.0,
            'chart' => [
                'labels' => array_column($ringkasan, 'nama'),
                'pagu' => array_column($ringkasan, 'pagu'),
                'realisasi' => array_column($ringkasan, 'realisasi'),
            ],
        ]);
    }
}
