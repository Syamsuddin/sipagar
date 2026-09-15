<x-layout.app title="Laporan Monev Triwulan">
    @include('laporan._filter', ['kolom' => ['tahun', 'triwulan', 'bidang', 'sumber_dana'], 'ikon' => 'fa-table-list', 'judul' => 'Monev Triwulan'])
    <x-card title="Monev Triwulan {{ $filter?->triwulan }} · {{ $filter?->tahun->tahun }}" icon="fa-table-list">
        <x-slot:aksi>@if ($hasil)<x-badge color="green">{{ $hasil['jumlah'] }} sub kegiatan</x-badge>@endif</x-slot:aksi>
        @if (! $filter)
            <x-empty-state icon="fa-calendar" text="Belum ada tahun anggaran." />
        @elseif ($hasil['jumlah'] === 0)
            <x-empty-state />
        @else
            <x-data-table>
                <x-slot:head><th>No</th><th>Kode</th><th>Sub Kegiatan</th><th class="hide-mobile">Bidang</th><th class="hide-mobile">Sumber</th><th>Pagu</th><th>Target Keu</th><th>Realisasi Keu</th><th>Dev. Keu</th><th class="hide-mobile">Target Fisik</th><th class="hide-mobile">Real. Fisik</th><th class="hide-mobile">Dev. Fisik</th><th>Status</th></x-slot:head>
                @foreach ($hasil['kelompok'] as $g)
                    <tr class="bg-[rgba(0,230,138,0.03)]"><td></td><td class="font-bold text-[var(--accent)] whitespace-nowrap">{{ $g['program']['kode'] }}</td><td class="font-bold" colspan="11">{{ $g['program']['nama'] }}</td></tr>
                    @foreach ($g['baris'] as $b)
                        <tr>
                            <td class="text-[var(--fg-muted)]">{{ $b['no'] }}</td>
                            <td class="text-[var(--fg-muted)] text-[0.78rem] whitespace-nowrap !pl-8">{{ $b['kode'] }}</td>
                            <td class="font-semibold">{{ $b['nama'] }}</td>
                            <td class="hide-mobile"><x-badge color="green">{{ $b['bidang'] }}</x-badge></td>
                            <td class="hide-mobile text-[var(--fg-muted)]">{{ $b['sumber'] }}</td>
                            <td class="big-number !text-[0.85rem] whitespace-nowrap">{{ rupiah($b['pagu']) }}</td>
                            <td class="big-number !text-[0.85rem] whitespace-nowrap">{{ rupiah($b['target_keu']) }} <span class="text-[var(--fg-muted)]">({{ $b['target_keu_persen'] }}%)</span></td>
                            <td class="big-number !text-[0.85rem] whitespace-nowrap text-[var(--danger)]">{{ rupiah($b['realisasi']) }} <span class="text-[var(--fg-muted)]">({{ $b['realisasi_persen'] }}%)</span></td>
                            <td class="big-number !text-[0.85rem] {{ $b['deviasi_keu'] < 0 ? 'text-[var(--danger)]' : 'text-[var(--accent)]' }}">{{ $b['deviasi_keu'] > 0 ? '+' : '' }}{{ $b['deviasi_keu'] }}%</td>
                            <td class="hide-mobile big-number !text-[0.85rem]">{{ number_format($b['target_fisik'], 2, ',', '.') }}%</td>
                            <td class="hide-mobile big-number !text-[0.85rem]">{{ number_format($b['realisasi_fisik'], 2, ',', '.') }}%</td>
                            <td class="hide-mobile big-number !text-[0.85rem] {{ $b['deviasi_fisik'] < 0 ? 'text-[var(--danger)]' : 'text-[var(--accent)]' }}">{{ $b['deviasi_fisik'] > 0 ? '+' : '' }}{{ number_format($b['deviasi_fisik'], 2, ',', '.') }}</td>
                            <td><x-badge :color="$b['status']->warnaBadge()">{{ $b['status']->label() }}</x-badge></td>
                        </tr>
                    @endforeach
                    @php $s = $g['subtotal']; @endphp
                    <tr class="font-bold"><td colspan="5" class="text-[var(--fg-muted)]">Subtotal {{ $g['program']['kode'] }}</td><td class="big-number !text-[0.85rem]">{{ rupiah($s['pagu']) }}</td><td class="big-number !text-[0.85rem]">{{ rupiah($s['target_keu']) }} ({{ $s['target_keu_persen'] }}%)</td><td class="big-number !text-[0.85rem] text-[var(--danger)]">{{ rupiah($s['realisasi']) }} ({{ $s['realisasi_persen'] }}%)</td><td class="big-number !text-[0.85rem]">{{ $s['deviasi_keu'] }}%</td><td colspan="3" class="hide-mobile"></td><td><x-badge :color="$s['status']->warnaBadge()">{{ $s['status']->label() }}</x-badge></td></tr>
                @endforeach
                @php $t = $hasil['total']; @endphp
                <x-slot:foot><td colspan="5">TOTAL</td><td class="big-number !text-[0.85rem]">{{ rupiah($t['pagu']) }}</td><td class="big-number !text-[0.85rem]">{{ rupiah($t['target_keu']) }} ({{ $t['target_keu_persen'] }}%)</td><td class="big-number !text-[0.85rem] text-[var(--danger)]">{{ rupiah($t['realisasi']) }} ({{ $t['realisasi_persen'] }}%)</td><td class="big-number !text-[0.85rem]">{{ $t['deviasi_keu'] }}%</td><td colspan="3" class="hide-mobile"></td><td><x-badge :color="$t['status']->warnaBadge()">{{ $t['status']->label() }}</x-badge></td></x-slot:foot>
            </x-data-table>
        @endif
    </x-card>
</x-layout.app>
