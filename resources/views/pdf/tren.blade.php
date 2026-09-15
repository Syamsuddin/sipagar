@php $f = $hasil['filter']; $lalu = $hasil['tahun_lalu']; @endphp
<x-layout.pdf :judul="'Tren Serapan Anggaran Bulanan'" :subjudul="'Tahun Anggaran '.$f->tahun->tahun.' · Pagu '.rupiah($hasil['pagu']).($lalu ? ' · pembanding '.$lalu : '')" :pengaturan="$pengaturan" :printCss="$printCss" :dicetak="$dicetak">
    <table class="data">
        <thead><tr><th>Bulan</th><th>Realisasi Kumulatif (Rp)</th><th>%</th><th>Target Kumulatif (Rp)</th><th>%</th><th>Deviasi (%)</th><th>Realisasi {{ $lalu ?? 'Thn lalu' }} (Rp)</th><th>%</th></tr></thead>
        <tbody>
        @if ($hasil['pagu'] === 0)
            <tr><td colspan="8" class="kosong">Tidak ada data</td></tr>
        @else
            @foreach ($hasil['baris'] as $b)
                <tr><td>{{ $b['bulan'] }}</td><td class="angka">{{ number_format($b['realisasi'], 0, ',', '.') }}</td><td class="angka">{{ $b['realisasi_persen'] }}</td><td class="angka">{{ number_format($b['target'], 0, ',', '.') }}</td><td class="angka">{{ $b['target_persen'] }}</td><td class="angka">{{ $b['deviasi'] }}</td><td class="angka">{{ number_format($b['realisasi_lalu'], 0, ',', '.') }}</td><td class="angka">{{ $b['realisasi_lalu_persen'] }}</td></tr>
            @endforeach
        @endif
        </tbody>
    </table>
</x-layout.pdf>
