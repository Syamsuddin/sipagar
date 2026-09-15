<?php

if (! function_exists('rupiah')) {
    /**
     * Format rupiah bulat: 1234567 → "Rp 1.234.567" (docs/12 §Uang).
     */
    function rupiah(int $nilai): string
    {
        $tanda = $nilai < 0 ? '-' : '';

        return $tanda.'Rp '.number_format(abs($nilai), 0, ',', '.');
    }
}

if (! function_exists('rupiah_singkat')) {
    /**
     * Format singkat untuk stat-card, identik fRs prototipe: "Rp 2.8 M", "Rp 678.0 Jt", "Rp 12 Rb".
     */
    function rupiah_singkat(int $nilai): string
    {
        if ($nilai >= 1_000_000_000) {
            return 'Rp '.number_format($nilai / 1_000_000_000, 1, '.', '').' M';
        }
        if ($nilai >= 1_000_000) {
            return 'Rp '.number_format($nilai / 1_000_000, 1, '.', '').' Jt';
        }
        if ($nilai >= 1_000) {
            return 'Rp '.number_format($nilai / 1_000, 0, '.', '').' Rb';
        }

        return 'Rp '.$nilai;
    }
}

if (! function_exists('parseRupiah')) {
    /**
     * "Rp 1.234.567" / "1234567" / "1.234.567,00" → 1234567 (docs/16 #6: jangan intval pada string bertitik).
     */
    function parseRupiah(string|int|null $teks): int
    {
        if (is_int($teks)) {
            return $teks;
        }
        $bersih = preg_replace('/,\d{1,2}$/', '', trim((string) $teks)) ?? '';
        $angka = preg_replace('/[^\d-]/', '', $bersih) ?? '';

        return $angka === '' || $angka === '-' ? 0 : (int) $angka;
    }
}
