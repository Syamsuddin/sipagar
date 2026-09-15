<x-layout.app title="Tren Serapan">
    @include('laporan._filter', ['kolom' => ['tahun', 'bidang'], 'ikon' => 'fa-chart-line', 'judul' => 'Tren Serapan Bulanan'])
    @if (! $filter)
        <x-card><x-empty-state icon="fa-calendar" text="Belum ada tahun anggaran." /></x-card>
    @elseif ($hasil['pagu'] === 0)
        <x-card title="Tren Serapan {{ $filter->tahun->tahun }}" icon="fa-chart-line"><x-empty-state /></x-card>
    @else
        <x-card title="Kurva Kumulatif {{ $filter->tahun->tahun }} (% pagu {{ rupiah_singkat($hasil['pagu']) }})" icon="fa-chart-line" class="mb-5">
            <div class="relative h-[320px]" x-data="grafikGaris(@js($hasil['grafik'] + ['tahun' => $filter->tahun->tahun, 'tahunLalu' => $hasil['tahun_lalu']]))"><canvas x-ref="kanvas"></canvas></div>
        </x-card>
        <x-card title="Tabel 12 Bulan" icon="fa-table">
            <x-data-table>
                <x-slot:head><th>Bulan</th><th>Realisasi Kumulatif</th><th>%</th><th class="hide-mobile">Target Kumulatif</th><th>Target %</th><th>Deviasi</th><th class="hide-mobile">Realisasi {{ $hasil['tahun_lalu'] ?? 'tahun lalu' }}</th><th class="hide-mobile">%</th></x-slot:head>
                @foreach ($hasil['baris'] as $b)
                    <tr>
                        <td class="font-semibold">{{ $b['bulan'] }}</td>
                        <td class="big-number !text-[0.85rem] whitespace-nowrap text-[var(--danger)]">{{ rupiah($b['realisasi']) }}</td>
                        <td class="big-number !text-[0.85rem] text-[#00b4d8]">{{ $b['realisasi_persen'] }}%</td>
                        <td class="hide-mobile big-number !text-[0.85rem] whitespace-nowrap">{{ rupiah($b['target']) }}</td>
                        <td class="big-number !text-[0.85rem]">{{ $b['target_persen'] }}%</td>
                        <td class="big-number !text-[0.85rem] {{ $b['deviasi'] < 0 ? 'text-[var(--danger)]' : 'text-[var(--accent)]' }}">{{ $b['deviasi'] > 0 ? '+' : '' }}{{ $b['deviasi'] }}%</td>
                        <td class="hide-mobile big-number !text-[0.85rem] whitespace-nowrap text-[var(--fg-muted)]">{{ rupiah($b['realisasi_lalu']) }}</td>
                        <td class="hide-mobile big-number !text-[0.85rem] text-[var(--fg-muted)]">{{ $b['realisasi_lalu_persen'] }}%</td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-card>
    @endif
</x-layout.app>
