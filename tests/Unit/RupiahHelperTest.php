<?php

test('rupiah memformat bulat dengan pemisah titik', function (int $nilai, string $harapan) {
    expect(rupiah($nilai))->toBe($harapan);
})->with([
    [0, 'Rp 0'],
    [1_234_567, 'Rp 1.234.567'],
    [2_400_000_000, 'Rp 2.400.000.000'],
    [-500, '-Rp 500'],
]);

test('rupiah_singkat identik fRs prototipe', function (int $nilai, string $harapan) {
    expect(rupiah_singkat($nilai))->toBe($harapan);
})->with([
    [2_770_000_000, 'Rp 2.8 M'],
    [678_000_000, 'Rp 678.0 Jt'],
    [12_000, 'Rp 12 Rb'],
    [500, 'Rp 500'],
]);
