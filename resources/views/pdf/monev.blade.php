@php $f = $hasil['filter']; @endphp
<x-layout.pdf :judul="'Laporan Monitoring & Evaluasi Anggaran Triwulan '.$f->triwulan" :subjudul="'Tahun Anggaran '.$f->tahun->tahun.' · s.d. '.app(\App\Services\SerapanCalculator::class)->akhirTriwulan($f->tahun->tahun, $f->triwulan)->translatedFormat('d F Y')" :pengaturan="$pengaturan" :printCss="$printCss" :dicetak="$dicetak">
    <table class="data">
        <thead><tr><th>No</th><th>Kode</th><th>Sub Kegiatan</th><th>Bidang</th><th>Sumber</th><th>Pagu</th><th>Target Keu (Rp)</th><th>%</th><th>Realisasi Keu (Rp)</th><th>%</th><th>Dev. Keu (%)</th><th>Target Fisik (%)</th><th>Real. Fisik (%)</th><th>Dev. Fisik</th><th>Status</th></tr></thead>
        <tbody>
        @forelse ($hasil['kelompok'] as $g)
            <tr class="kelompok"><td></td><td>{{ $g['program']['kode'] }}</td><td colspan="13">{{ $g['program']['nama'] }}</td></tr>
            @foreach ($g['baris'] as $b)
                <tr><td class="tengah">{{ $b['no'] }}</td><td>{{ $b['kode'] }}</td><td>{{ $b['nama'] }}</td><td class="tengah">{{ $b['bidang'] }}</td><td class="tengah">{{ $b['sumber'] }}</td><td class="angka">{{ number_format($b['pagu'], 0, ',', '.') }}</td><td class="angka">{{ number_format($b['target_keu'], 0, ',', '.') }}</td><td class="angka">{{ $b['target_keu_persen'] }}</td><td class="angka">{{ number_format($b['realisasi'], 0, ',', '.') }}</td><td class="angka">{{ $b['realisasi_persen'] }}</td><td class="angka">{{ $b['deviasi_keu'] }}</td><td class="angka">{{ number_format($b['target_fisik'], 2, ',', '.') }}</td><td class="angka">{{ number_format($b['realisasi_fisik'], 2, ',', '.') }}</td><td class="angka">{{ number_format($b['deviasi_fisik'], 2, ',', '.') }}</td><td class="tengah">{{ $b['status']->label() }}</td></tr>
            @endforeach
            @php $s = $g['subtotal']; @endphp
            <tr class="subtotal"><td colspan="5">Subtotal {{ $g['program']['kode'] }}</td><td class="angka">{{ number_format($s['pagu'], 0, ',', '.') }}</td><td class="angka">{{ number_format($s['target_keu'], 0, ',', '.') }}</td><td class="angka">{{ $s['target_keu_persen'] }}</td><td class="angka">{{ number_format($s['realisasi'], 0, ',', '.') }}</td><td class="angka">{{ $s['realisasi_persen'] }}</td><td class="angka">{{ $s['deviasi_keu'] }}</td><td colspan="3"></td><td class="tengah">{{ $s['status']->label() }}</td></tr>
        @empty
            <tr><td colspan="15" class="kosong">Tidak ada data</td></tr>
        @endforelse
        @if ($hasil['jumlah'] > 0)
            @php $t = $hasil['total']; @endphp
            <tr class="total"><td colspan="5">TOTAL</td><td class="angka">{{ number_format($t['pagu'], 0, ',', '.') }}</td><td class="angka">{{ number_format($t['target_keu'], 0, ',', '.') }}</td><td class="angka">{{ $t['target_keu_persen'] }}</td><td class="angka">{{ number_format($t['realisasi'], 0, ',', '.') }}</td><td class="angka">{{ $t['realisasi_persen'] }}</td><td class="angka">{{ $t['deviasi_keu'] }}</td><td colspan="3"></td><td class="tengah">{{ $t['status']->label() }}</td></tr>
        @endif
        </tbody>
    </table>
</x-layout.pdf>
