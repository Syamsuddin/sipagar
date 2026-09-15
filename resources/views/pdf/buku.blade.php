@php $f = $hasil['filter']; @endphp
<x-layout.pdf :judul="'Buku Realisasi Anggaran'" :subjudul="'Tahun Anggaran '.$f->tahun->tahun.' · '.$f->dari->format('d/m/Y').' s.d. '.$f->sampai->format('d/m/Y')" :pengaturan="$pengaturan" :printCss="$printCss" :dicetak="$dicetak">
    <table class="data">
        <thead><tr><th>No</th><th>Tanggal</th><th>Kode</th><th>Sub Kegiatan</th><th>Bidang</th><th>Uraian</th><th>No. SP2D</th><th>Jumlah</th><th>Lamp.</th><th>Dicatat oleh</th></tr></thead>
        <tbody>
        @forelse ($hasil['baris'] as $b)
            <tr><td class="tengah">{{ $b['no'] }}</td><td class="tengah">{{ $b['tanggal'] }}</td><td>{{ $b['kode'] }}</td><td>{{ $b['nama'] }}</td><td class="tengah">{{ $b['bidang'] }}</td><td>{{ $b['uraian'] }}</td><td>{{ $b['no_sp2d'] ?? '-' }}</td><td class="angka">{{ number_format($b['jumlah'], 0, ',', '.') }}</td><td class="tengah">{{ $b['lampiran'] ? 'Ada' : '-' }}</td><td>{{ $b['dicatat_oleh'] }}</td></tr>
        @empty
            <tr><td colspan="10" class="kosong">Tidak ada data</td></tr>
        @endforelse
        @if ($hasil['jumlah'] > 0)
            <tr class="total"><td colspan="7">TOTAL ({{ $hasil['jumlah'] }} transaksi)</td><td class="angka">{{ number_format($hasil['total'], 0, ',', '.') }}</td><td colspan="2"></td></tr>
        @endif
        </tbody>
    </table>
</x-layout.pdf>
