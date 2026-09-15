<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · SIPAGAR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<div class="auth-screen">
    <div class="bg-mesh"></div>
    <div class="bg-grid"></div>
    <div class="auth-particles" x-data="partikel(30)"></div>
    <div class="auth-container">
        <div class="auth-brand">
            <div class="auth-brand-icon"><i class="fa-solid fa-building-columns"></i></div>
            <h1>SIPAGAR</h1>
            <p>Sistem Informasi Pagu Anggaran &amp; Realisasi</p>
            <p class="!text-[0.72rem] !mt-[2px] opacity-60">BKPSDM Kabupaten Hulu Sungai Selatan</p>
        </div>
        <div class="auth-box">
            <div class="auth-panel active">
                {{ $slot }}
            </div>
        </div>
        <div class="auth-info">SIPAGAR v2.0 &middot; BKPSDM Kab. Hulu Sungai Selatan</div>
    </div>
</div>

<x-toast />

</body>
</html>
