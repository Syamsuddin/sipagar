<x-layout.app title="Dashboard">
    @php $t = $hasil['total']; @endphp
    <x-card class="mb-5">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="font-bold text-[0.95rem] flex items-center gap-2"><i class="fa-solid fa-filter text-[var(--accent)] text-[0.85rem]"></i>Filter Dashboard</div>
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="tahun">Tahun:</label>
                    <select class="form-input !w-[130px] !py-2 !px-3" id="tahun" name="tahun" onchange="this.form.submit()">
                        @foreach ($daftarTahun as $ta)<option value="{{ $ta->tahun }}" @selected($tahunTerpilih === $ta->tahun)>{{ $ta->tahun }}</option>@endforeach
                        <option value="semua" @selected($tahunTerpilih === 'semua')>Semua</option>
                    </select></div>
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="bidang">Bidang:</label>
                    <select class="form-input !w-[220px] !py-2 !px-3" id="bidang" name="bidang" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @foreach ($daftarBidang as $b)<option value="{{ $b->id }}" @selected($bidangTerpilih === $b->id)>{{ $b->nama }}</option>@endforeach
                    </select></div>
            </form>
        </div>
    </x-card>

    <div class="stat-grid grid grid-cols-4 gap-4 mb-7">
        <x-stat-card color="green" icon="fa-coins" label="Total Pagu" :value="rupiah_singkat($t['pagu'])" :sub="$t['jumlah_sub'].' sub kegiatan'" />
        <x-stat-card color="red" icon="fa-arrow-trend-down" label="Total Realisasi" :value="rupiah_singkat($t['realisasi'])" :sub="$t['jumlah_transaksi'].' transaksi'" />
        <x-stat-card color="yellow" icon="fa-wallet" label="Sisa Anggaran" :value="rupiah_singkat($t['sisa'])" :sub="$t['sisa_persen'].'% tersisa'" />
        <x-stat-card color="teal" icon="fa-gauge-high" label="Serapan" :value="$t['serapan'].'%'">
            <x-progress :value="$t['serapan']" />
        </x-stat-card>
    </div>

    <div class="chart-grid grid grid-cols-2 gap-4 mb-7">
        <x-card title="Pagu vs Realisasi" icon="fa-chart-bar">
            @if ($hasil['baris']->isEmpty())
                <x-empty-state icon="fa-chart-bar" />
            @else
                <div class="relative h-[300px]" x-data="grafikBar(@js($hasil['grafik_bar']))"><canvas x-ref="kanvas"></canvas></div>
            @endif
        </x-card>
        <x-card title="Distribusi Pagu" icon="fa-chart-pie">
            @if ($hasil['baris']->isEmpty())
                <x-empty-state icon="fa-chart-pie" />
            @else
                <div class="relative h-[300px]" x-data="grafikDoughnut(@js($hasil['grafik_doughnut']))"><canvas x-ref="kanvas"></canvas></div>
            @endif
        </x-card>
    </div>

    <x-card title="Ringkasan" icon="fa-list-check">
        @if ($hasil['baris']->isEmpty())
            <x-empty-state />
        @else
            <x-data-table>
                <x-slot:head><th>No</th><th>Kode</th><th>Sub Kegiatan</th><th class="hide-mobile">Bidang</th><th class="hide-mobile">Sumber Dana</th><th>Pagu</th><th>Realisasi</th><th>Sisa</th><th>Serapan</th><th>Status</th></x-slot:head>
                @foreach ($hasil['baris'] as $r)
                    <tr>
                        <td class="text-[var(--fg-muted)] font-semibold">{{ $r['no'] }}</td>
                        <td class="text-[var(--fg-muted)] text-[0.78rem] whitespace-nowrap">{{ $r['kode'] }}</td>
                        <td class="font-semibold">{{ $r['nama'] }}</td>
                        <td class="hide-mobile"><x-badge color="green">{{ $r['bidang'] }}</x-badge></td>
                        <td class="hide-mobile"><x-badge-sumber :sumber="$r['sumber']" /></td>
                        <td class="big-number !text-[0.85rem]">{{ rupiah($r['pagu']) }}</td>
                        <td class="big-number !text-[0.85rem] text-[var(--danger)]">{{ rupiah($r['realisasi']) }}</td>
                        <td class="big-number !text-[0.85rem] {{ $r['sisa'] >= 0 ? 'text-[var(--warning)]' : 'text-[var(--danger)]' }}">{{ rupiah($r['sisa']) }}</td>
                        <td class="big-number !text-[0.85rem] text-[#00b4d8]">{{ $r['serapan'] }}%</td>
                        <td><x-badge :color="$r['status']->warnaBadge()">{{ $r['status']->label() }}</x-badge></td>
                    </tr>
                @endforeach
                <x-slot:foot><td colspan="5">Total</td><td class="big-number !text-[0.85rem]">{{ rupiah($t['pagu']) }}</td><td class="big-number !text-[0.85rem] text-[var(--danger)]">{{ rupiah($t['realisasi']) }}</td><td class="big-number !text-[0.85rem] text-[var(--warning)]">{{ rupiah($t['sisa']) }}</td><td class="big-number !text-[0.85rem] text-[#00b4d8]">{{ $t['serapan'] }}%</td><td><x-badge :color="\App\Enums\StatusSerapan::dariPersen($t['serapan'])->warnaBadge()">{{ \App\Enums\StatusSerapan::dariPersen($t['serapan'])->label() }}</x-badge></td></x-slot:foot>
            </x-data-table>
        @endif
    </x-card>
</x-layout.app>
