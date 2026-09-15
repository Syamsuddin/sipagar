<x-layout.app title="Buku Realisasi">
    @include('laporan._filter', ['kolom' => ['tahun', 'triwulan', 'tanggal', 'bidang', 'sub_kegiatan'], 'ikon' => 'fa-book', 'judul' => 'Buku Realisasi'])
    <x-card title="Rincian Transaksi {{ $filter?->tahun->tahun }}" icon="fa-book">
        <x-slot:aksi>@if ($hasil)<x-badge color="red">{{ $hasil['jumlah'] }} transaksi</x-badge>@endif</x-slot:aksi>
        @if (! $filter)
            <x-empty-state icon="fa-calendar" text="Belum ada tahun anggaran." />
        @elseif ($hasil['jumlah'] === 0)
            <x-empty-state icon="fa-file-circle-plus" text="Belum ada realisasi." />
        @else
            @php $p = $hasil['halaman']; $awal = ($p->currentPage() - 1) * $p->perPage(); @endphp
            <x-data-table>
                <x-slot:head><th>No</th><th>Tanggal</th><th class="hide-mobile">Kode</th><th>Sub Kegiatan</th><th class="hide-mobile">Uraian</th><th class="hide-mobile">No. SP2D</th><th>Jumlah</th><th class="hide-mobile">Lampiran</th><th class="hide-mobile">Dicatat oleh</th></x-slot:head>
                @foreach ($p as $i => $r)
                    @php $b = \App\Queries\BukuRealisasiQuery::baris($r, $awal + $i + 1); @endphp
                    <tr>
                        <td class="text-[var(--fg-muted)]">{{ $b['no'] }}</td>
                        <td class="whitespace-nowrap">{{ $b['tanggal'] }}</td>
                        <td class="hide-mobile text-[var(--fg-muted)] text-[0.78rem] whitespace-nowrap">{{ $b['kode'] }}</td>
                        <td class="font-semibold">{{ $b['nama'] }} <x-badge color="green" class="hide-mobile">{{ $b['bidang'] }}</x-badge></td>
                        <td class="hide-mobile text-[var(--fg-muted)]">{{ $b['uraian'] }}</td>
                        <td class="hide-mobile text-[var(--fg-muted)]">{{ $b['no_sp2d'] ?? '—' }}</td>
                        <td class="big-number !text-[0.85rem] whitespace-nowrap text-[var(--danger)]">{{ rupiah($b['jumlah']) }}</td>
                        <td class="hide-mobile">@if ($b['lampiran'])<a class="btn-secondary no-underline !py-1 !px-2 !text-[0.7rem]" href="{{ route('realisasi.lampiran.show', $b['id']) }}" target="_blank"><i class="fa-solid fa-paperclip"></i></a>@else —@endif</td>
                        <td class="hide-mobile text-[var(--fg-muted)]">{{ $b['dicatat_oleh'] }}</td>
                    </tr>
                @endforeach
                <x-slot:foot><td colspan="6">TOTAL ({{ $hasil['jumlah'] }} transaksi)</td><td class="big-number !text-[0.9rem] text-[var(--danger)]">{{ rupiah($hasil['total']) }}</td><td colspan="2" class="hide-mobile"></td></x-slot:foot>
            </x-data-table>
            @if ($p->hasPages())
                <div class="flex items-center justify-between flex-wrap gap-3 mt-4">
                    <span class="text-[0.78rem] text-[var(--fg-muted)]">Halaman {{ $p->currentPage() }} dari {{ $p->lastPage() }}</span>
                    <div class="flex gap-2">
                        @if ($p->onFirstPage())<span class="btn-secondary opacity-50">‹ Sebelumnya</span>@else<a class="btn-secondary no-underline" href="{{ $p->previousPageUrl() }}">‹ Sebelumnya</a>@endif
                        @if ($p->hasMorePages())<a class="btn-secondary no-underline" href="{{ $p->nextPageUrl() }}">Berikutnya ›</a>@else<span class="btn-secondary opacity-50">Berikutnya ›</span>@endif
                    </div>
                </div>
            @endif
        @endif
    </x-card>
</x-layout.app>
