<x-layout.app title="Laporan Rekap">
    @include('laporan._filter', ['kolom' => ['tahun', 'triwulan'], 'ikon' => 'fa-layer-group', 'judul' => 'Rekap Sumber Dana & Bidang'])
    @if (! $filter)
        <x-card><x-empty-state icon="fa-calendar" text="Belum ada tahun anggaran." /></x-card>
    @else
        @php $t = $hasil['total']; @endphp
        <div class="stat-grid grid grid-cols-4 gap-4 mb-7">
            <x-stat-card color="green" icon="fa-coins" label="Total Pagu" :value="rupiah_singkat($t['pagu'])" :sub="$t['jumlah_sub'].' sub kegiatan'" />
            <x-stat-card color="red" icon="fa-arrow-trend-down" label="Realisasi s.d. TW{{ $filter->triwulan }}" :value="rupiah_singkat($t['realisasi'])" />
            <x-stat-card color="yellow" icon="fa-wallet" label="Sisa" :value="rupiah_singkat($t['sisa'])" />
            <x-stat-card color="teal" icon="fa-gauge-high" label="Serapan" :value="$t['serapan'].'%'"><x-progress :value="$t['serapan']" /></x-stat-card>
        </div>
        <div class="chart-grid grid grid-cols-2 gap-4">
            @foreach (['sumber_dana' => ['Per Sumber Dana', 'fa-coins', 'Sumber Dana'], 'bidang' => ['Per Bidang', 'fa-sitemap', 'Bidang']] as $kunci => [$judul, $ikon, $kolomNama])
                <x-card :title="$judul" :icon="$ikon" class="min-w-0">
                    @if ($hasil[$kunci]->isEmpty())
                        <x-empty-state />
                    @else
                        <x-data-table>
                            <x-slot:head><th>No</th><th>{{ $kolomNama }}</th><th class="hide-mobile">Sub</th><th>Pagu</th><th>Realisasi</th><th class="hide-mobile">Sisa</th><th>Serapan</th><th>Status</th></x-slot:head>
                            @foreach ($hasil[$kunci] as $r)
                                <tr>
                                    <td class="text-[var(--fg-muted)]">{{ $r['no'] }}</td>
                                    <td class="font-semibold"><span class="text-[var(--fg-muted)] text-[0.75rem] block">{{ $r['kode'] }}</span>{{ $r['nama'] }}</td>
                                    <td class="hide-mobile text-[var(--fg-muted)]">{{ $r['jumlah_sub'] }}</td>
                                    <td class="big-number !text-[0.85rem] whitespace-nowrap">{{ rupiah($r['pagu']) }}</td>
                                    <td class="big-number !text-[0.85rem] whitespace-nowrap text-[var(--danger)]">{{ rupiah($r['realisasi']) }}</td>
                                    <td class="hide-mobile big-number !text-[0.85rem] whitespace-nowrap text-[var(--warning)]">{{ rupiah($r['sisa']) }}</td>
                                    <td class="big-number !text-[0.85rem] text-[#00b4d8]">{{ $r['serapan'] }}%</td>
                                    <td><x-badge :color="$r['status']->warnaBadge()">{{ $r['status']->label() }}</x-badge></td>
                                </tr>
                            @endforeach
                            <x-slot:foot><td colspan="2">TOTAL</td><td class="hide-mobile">{{ $t['jumlah_sub'] }}</td><td class="big-number !text-[0.85rem]">{{ rupiah($t['pagu']) }}</td><td class="big-number !text-[0.85rem] text-[var(--danger)]">{{ rupiah($t['realisasi']) }}</td><td class="hide-mobile big-number !text-[0.85rem] text-[var(--warning)]">{{ rupiah($t['sisa']) }}</td><td class="big-number !text-[0.85rem] text-[#00b4d8]">{{ $t['serapan'] }}%</td><td><x-badge :color="$t['status']->warnaBadge()">{{ $t['status']->label() }}</x-badge></td></x-slot:foot>
                        </x-data-table>
                    @endif
                </x-card>
            @endforeach
        </div>
    @endif
</x-layout.app>
