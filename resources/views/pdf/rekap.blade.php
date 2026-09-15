@php $f = $hasil['filter']; $t = $hasil['total']; @endphp
<x-layout.pdf :judul="'Rekapitulasi Anggaran per Sumber Dana dan per Bidang'" :subjudul="'Tahun Anggaran '.$f->tahun->tahun.' · s.d. Triwulan '.$f->triwulan" :pengaturan="$pengaturan" :printCss="$printCss" :dicetak="$dicetak">
    @foreach (['sumber_dana' => 'Per Sumber Dana', 'bidang' => 'Per Bidang'] as $kunci => $judulTabel)
        <h3 style="font-size:10pt;margin:10px 0 4px;">{{ $judulTabel }}</h3>
        <table class="data">
            <thead><tr><th>No</th><th>Kode</th><th>{{ $kunci === 'bidang' ? 'Bidang' : 'Sumber Dana' }}</th><th>Sub Keg.</th><th>Pagu</th><th>Realisasi</th><th>Sisa</th><th>Serapan (%)</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($hasil[$kunci] as $r)
                <tr><td class="tengah">{{ $r['no'] }}</td><td>{{ $r['kode'] }}</td><td>{{ $r['nama'] }}</td><td class="tengah">{{ $r['jumlah_sub'] }}</td><td class="angka">{{ number_format($r['pagu'], 0, ',', '.') }}</td><td class="angka">{{ number_format($r['realisasi'], 0, ',', '.') }}</td><td class="angka">{{ number_format($r['sisa'], 0, ',', '.') }}</td><td class="angka">{{ $r['serapan'] }}</td><td class="tengah">{{ $r['status']->label() }}</td></tr>
            @empty
                <tr><td colspan="9" class="kosong">Tidak ada data</td></tr>
            @endforelse
            @if ($t['jumlah_sub'] > 0)
                <tr class="total"><td colspan="3">TOTAL</td><td class="tengah">{{ $t['jumlah_sub'] }}</td><td class="angka">{{ number_format($t['pagu'], 0, ',', '.') }}</td><td class="angka">{{ number_format($t['realisasi'], 0, ',', '.') }}</td><td class="angka">{{ number_format($t['sisa'], 0, ',', '.') }}</td><td class="angka">{{ $t['serapan'] }}</td><td class="tengah">{{ $t['status']->label() }}</td></tr>
            @endif
            </tbody>
        </table>
    @endforeach
</x-layout.pdf>
