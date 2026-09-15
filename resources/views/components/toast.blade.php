{{-- <x-toast> — flash session: sukses → toast-success, gagal → toast-error, info → toast-info; hilang 3 s --}}
@php
    $daftar = array_filter([
        'success' => session('sukses'),
        'error' => session('gagal'),
        'info' => session('info'),
    ]);
    $ikon = ['success' => 'fa-check-circle', 'error' => 'fa-circle-xmark', 'info' => 'fa-circle-info'];
@endphp
<div class="toast-container">
    @foreach ($daftar as $jenis => $pesan)
        <div class="toast toast-{{ $jenis }}" x-data="toast()" x-show="tampil" :style="keluar ? 'animation: toastOut 0.3s ease forwards' : ''"><i class="fa-solid {{ $ikon[$jenis] }}"></i> {{ $pesan }}</div>
    @endforeach
</div>
