<?php

// Rentang tahun anggaran (docs/02, docs/10). Nilai dari .env SIPAGAR_TAHUN_MIN/MAX.
return [
    'tahun_min' => (int) env('SIPAGAR_TAHUN_MIN', 2020),
    'tahun_max' => (int) env('SIPAGAR_TAHUN_MAX', 2034),
    'konfirmasi_sandi_menit' => 5,
];
