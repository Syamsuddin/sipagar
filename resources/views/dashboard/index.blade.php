<x-layout.app title="Dashboard">
    {{-- Filter tahun & bidang (docs/26 §Dashboard) — pola filter "Sisa Anggaran" prototipe; hidup di S3 --}}
    <x-card class="mb-5">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="font-bold text-[0.95rem] flex items-center gap-2"><i class="fa-solid fa-filter text-[var(--accent)] text-[0.85rem]"></i>Filter Dashboard</div>
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="tahun">Tahun:</label><select class="form-input !w-[130px] !py-2 !px-3" id="tahun" name="tahun"><option value="2025">2025</option></select></div>
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="bidang">Bidang:</label><select class="form-input !w-[220px] !py-2 !px-3" id="bidang" name="bidang"><option value="">Semua</option></select></div>
            </form>
        </div>
    </x-card>

    <div class="stat-grid grid grid-cols-4 gap-4 mb-7">
        <x-stat-card color="green" icon="fa-coins" label="Total Pagu" :value="rupiah_singkat($totalPagu)" :sub="count($ringkasan).' sub kegiatan'" />
        <x-stat-card color="red" icon="fa-arrow-trend-down" label="Total Realisasi" :value="rupiah_singkat($totalRealisasi)" sub="3 transaksi" />
        <x-stat-card color="yellow" icon="fa-wallet" label="Sisa Anggaran" :value="rupiah_singkat($sisa)" :sub="round(100 - $serapan).'% tersisa'" />
        <x-stat-card color="teal" icon="fa-gauge-high" label="Serapan" :value="round($serapan).'%'">
            <x-progress :value="$serapan" />
        </x-stat-card>
    </div>

    <div class="chart-grid grid grid-cols-2 gap-4 mb-7">
        <x-card title="Pagu vs Realisasi" icon="fa-chart-bar">
            <div class="relative h-[300px]" x-data="grafikBar(@js($chart))"><canvas x-ref="kanvas"></canvas></div>
        </x-card>
        <x-card title="Distribusi Pagu" icon="fa-chart-pie">
            <div class="relative h-[300px]" x-data="grafikDoughnut(@js(['labels' => $chart['labels'], 'data' => $chart['pagu']]))"><canvas x-ref="kanvas"></canvas></div>
        </x-card>
    </div>

    <x-card title="Ringkasan" icon="fa-list-check">
        @if (count($ringkasan) === 0)
            <x-empty-state />
        @else
            <x-data-table>
                <x-slot:head>
                    <th>No</th><th>Sub Kegiatan</th><th>Tahun</th><th>Sumber Dana</th><th>Pagu</th><th>Realisasi</th><th>Sisa</th><th>Serapan</th><th>Status</th>
                </x-slot:head>
                @foreach ($ringkasan as $i => $r)
                    <tr>
                        <td class="text-[var(--fg-muted)] font-semibold">{{ $i + 1 }}</td>
                        <td class="font-semibold">{{ $r['nama'] }}</td>
                        <td><x-badge color="green">{{ $r['tahun'] }}</x-badge></td>
                        <td><x-badge-sumber :sumber="['css_class' => 'sd-'.$r['sumber'], 'kode' => $r['sumber_label']]" /></td>
                        <td class="big-number !text-[0.85rem]">{{ rupiah($r['pagu']) }}</td>
                        <td class="big-number !text-[0.85rem] text-[var(--danger)]">{{ rupiah($r['realisasi']) }}</td>
                        <td class="big-number !text-[0.85rem] text-[var(--warning)]">{{ rupiah($r['pagu'] - $r['realisasi']) }}</td>
                        <td class="big-number !text-[0.85rem] text-[#00b4d8]">{{ $r['serapan'] }}%</td>
                        <td><x-badge :color="$r['status']">{{ $r['status_label'] }}</x-badge></td>
                    </tr>
                @endforeach
            </x-data-table>
        @endif
    </x-card>
</x-layout.app>
