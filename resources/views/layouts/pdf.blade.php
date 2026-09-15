<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>{!! $printCss !!}</style>
</head>
<body>
    <table class="kop">
        <tr>
            @if (! empty($pengaturan['kop_logo_path']) && is_file(storage_path('app/private/'.$pengaturan['kop_logo_path'])))
                <td class="logo"><img src="{{ storage_path('app/private/'.$pengaturan['kop_logo_path']) }}" alt="logo"></td>
            @endif
            <td>
                <h1>{{ $pengaturan['kop_nama_instansi'] ?? 'BKPSDM Kabupaten Hulu Sungai Selatan' }}</h1>
                @if (! empty($pengaturan['kop_alamat']))<p>{{ $pengaturan['kop_alamat'] }}</p>@endif
            </td>
        </tr>
    </table>

    <h2 class="judul">{{ $judul }}</h2>
    <p class="sub">{{ $subjudul }}</p>

    {{ $slot }}

    <table class="ttd">
        <tr>
            <td></td>
            <td class="blok">
                {{ $pengaturan['ttd_kota'] ?? 'Kandangan' }}, {{ $dicetak->translatedFormat('d F Y') }}<br>
                {{ $pengaturan['ttd_jabatan'] ?? '' }}
                <div class="nama">{{ $pengaturan['ttd_nama'] ?? '' }}</div>
                @if (! empty($pengaturan['ttd_nip']))NIP. {{ $pengaturan['ttd_nip'] }}@endif
            </td>
        </tr>
    </table>
    <p class="catatan">Dicetak dari SIPAGAR pada {{ $dicetak->format('d/m/Y H:i') }}.</p>
</body>
</html>
