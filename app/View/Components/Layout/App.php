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
     * Menu tab-nav (urutan & ikon: docs/26). Filter per peran menyusul di S1
     * lewat Policy; rute yang belum ada mengarah ke '#'.
     *
     * @return array<int, array{label: string, icon: string, href: string, aktif: bool}>
     */
    protected function menu(): array
    {
        $daftar = [
            ['Dashboard', 'fa-chart-pie', 'dashboard', 'dashboard'],
            ['Anggaran', 'fa-folder-open', 'anggaran.index', 'anggaran.*'],
            ['Target', 'fa-bullseye', 'target.index', 'target.*'],
            ['Realisasi', 'fa-receipt', 'realisasi.keuangan.index', 'realisasi.*'],
            ['Laporan', 'fa-file-lines', 'laporan.monev.index', 'laporan.*'],
            ['Master', 'fa-database', 'master.bidang.index', 'master.*'],
            ['Pengguna', 'fa-users', 'pengguna.index', 'pengguna.*'],
            ['Audit', 'fa-clock-rotate-left', 'audit-log.index', 'audit-log.*'],
        ];

        return array_map(fn (array $m) => [
            'label' => $m[0],
            'icon' => $m[1],
            'href' => Route::has($m[2]) ? route($m[2]) : '#',
            'aktif' => request()->routeIs($m[3]),
        ], $daftar);
    }

    public function render(): View
    {
        return view('layouts.app', ['menu' => $this->menu()]);
    }
}
