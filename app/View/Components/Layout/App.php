<?php

namespace App\View\Components\Layout;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

/**
 * <x-layout.app> → resources/views/layouts/app.blade.php (docs/12 + docs/26).
 */
class App extends Component
{
    public function __construct(public string $title = 'Dashboard') {}

    /**
     * Menu tab-nav (urutan & ikon docs/26; peran docs/05). Rute yang belum ada → '#'.
     *
     * @return array<int, array{label: string, icon: string, href: string, aktif: bool}>
     */
    protected function menu(): array
    {
        $admin = auth()->user()?->isAdmin() ?? false;

        $daftar = [
            ['Dashboard', 'fa-chart-pie', 'dashboard', 'dashboard', true],
            ['Anggaran', 'fa-folder-open', 'anggaran.index', 'anggaran.*', true],
            ['Target', 'fa-bullseye', 'target.index', 'target.*', true],
            ['Realisasi', 'fa-receipt', 'realisasi.keuangan.index', 'realisasi.*', true],
            ['Laporan', 'fa-file-lines', 'laporan.monev.index', 'laporan.*', true],
            ['Master', 'fa-database', 'master.bidang.index', 'master.*', $admin],
            ['Pengguna', 'fa-users', 'pengguna.index', 'pengguna.*', $admin],
            ['Audit', 'fa-clock-rotate-left', 'audit-log.index', 'audit-log.*', $admin],
        ];

        return array_values(array_map(fn (array $m) => [
            'label' => $m[0],
            'icon' => $m[1],
            'href' => Route::has($m[2]) ? route($m[2]) : '#',
            'aktif' => request()->routeIs($m[3]),
        ], array_filter($daftar, fn (array $m) => $m[4])));
    }

    /**
     * Sub-menu baris kedua (docs/26): Master, Realisasi, Laporan.
     *
     * @return array<int, array{label: string, icon: string, href: string, aktif: bool}>
     */
    protected function subMenu(): array
    {
        $daftar = match (true) {
            request()->routeIs('master.*') => [
                ['Bidang', 'fa-sitemap', 'master.bidang.index', 'master.bidang.*'],
                ['Sumber Dana', 'fa-coins', 'master.sumber-dana.index', 'master.sumber-dana.*'],
                ['Tahun Anggaran', 'fa-calendar', 'master.tahun-anggaran.index', 'master.tahun-anggaran.*'],
                ['Pengaturan', 'fa-gear', 'master.pengaturan.index', 'master.pengaturan.*'],
            ],
            request()->routeIs('laporan.*') => [
                ['Monev Triwulan', 'fa-table-list', 'laporan.monev.index', 'laporan.monev.*'],
                ['Rekap', 'fa-layer-group', 'laporan.rekap.index', 'laporan.rekap.*'],
                ['Buku Realisasi', 'fa-book', 'laporan.buku.index', 'laporan.buku.*'],
                ['Tren Serapan', 'fa-chart-line', 'laporan.tren.index', 'laporan.tren.*'],
            ],
            request()->routeIs('realisasi.*') => [
                ['Keuangan', 'fa-file-invoice-dollar', 'realisasi.keuangan.index', 'realisasi.keuangan.*'],
                ['Fisik', 'fa-percent', 'realisasi.fisik.index', 'realisasi.fisik.*'],
            ],
            default => [],
        };
        if ($daftar === []) {
            return [];
        }

        return array_map(fn (array $m) => [
            'label' => $m[0], 'icon' => $m[1], 'href' => route($m[2]), 'aktif' => request()->routeIs($m[3]),
        ], $daftar);
    }

    public function render(): View
    {
        return view('layouts.app', ['menu' => $this->menu(), 'subMenu' => $this->subMenu()]);
    }
}
