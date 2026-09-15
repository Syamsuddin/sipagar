<?php

use App\Services\SerapanCalculator;

// Rumus docs/04 yang sudah tersedia di S2; deviasi/fisik/status/agregat ditambah di S3.
beforeEach(fn () => $this->k = new SerapanCalculator);

test('serapan % dibulatkan; pagu 0 → 0', function (int $pagu, int $realisasi, int $harapan) {
    expect($this->k->serapanPersen($pagu, $realisasi))->toBe($harapan);
})->with([
    [100, 0, 0],
    [100, 49, 49],
    [300, 100, 33],
    [200, 101, 51],
    [0, 50, 0],
    [100, 150, 150],
]);

test('sisa & sisa %', function () {
    expect($this->k->sisa(100, 30))->toBe(70)
        ->and($this->k->sisa(100, 150))->toBe(-50)
        ->and($this->k->sisaPersen(100, 30))->toBe(70)
        ->and($this->k->sisaPersen(0, 0))->toBe(0);
});

test('target keuangan % TW', function () {
    expect($this->k->targetPersen(300_000_000, 50_000_000))->toBe(17)
        ->and($this->k->targetPersen(0, 5))->toBe(0);
});

test('status serapan sesuai ambang docs/04', function (int $pagu, int $realisasi, string $status) {
    expect($this->k->status($pagu, $realisasi)->value)->toBe($status);
})->with([
    [100, 59, 'aman'],
    [100, 60, 'sedang'],
    [100, 89, 'sedang'],
    [100, 90, 'kritis'],
    [100, 99, 'kritis'],
    [100, 100, 'habis'],
    [100, 120, 'habis'],
    [0, 0, 'aman'],
]);

test('fisik s.d. bulan m: bulan kosong → bulan terisi terakhir; tidak ada → 0', function () {
    $fisik = [1 => 10.0, 3 => 25.0, 6 => 40.0];
    expect($this->k->fisikSampaiBulan($fisik, 1))->toBe(10.0)
        ->and($this->k->fisikSampaiBulan($fisik, 2))->toBe(10.0)
        ->and($this->k->fisikSampaiBulan($fisik, 5))->toBe(25.0)
        ->and($this->k->fisikSampaiBulan($fisik, 12))->toBe(40.0)
        ->and($this->k->fisikSampaiBulan([], 6))->toBe(0.0)
        ->and($this->k->fisikSampaiBulan([9 => 80.0], 6))->toBe(0.0);
});

test('deviasi = kumulatif − kumulatif (keuangan Rp & poin, fisik poin)', function () {
    expect($this->k->deviasiKeuangan(80_000_000, 100_000_000))->toBe(-20_000_000)
        ->and($this->k->deviasiKeuanganPoin(400_000_000, 80_000_000, 100_000_000))->toBe(-5)
        ->and($this->k->deviasiFisik([3 => 20.0, 6 => 55.5], 2, 50.0))->toBe(5.5)
        ->and($this->k->deviasiFisik([], 1, 25.0))->toBe(-25.0);
});

test('akhir triwulan & agregat dari Σ bukan rata-rata %', function () {
    expect($this->k->akhirTriwulan(2025, 1)->toDateString())->toBe('2025-03-31')
        ->and($this->k->akhirTriwulan(2025, 2)->toDateString())->toBe('2025-06-30')
        ->and($this->k->akhirTriwulan(2025, 4)->toDateString())->toBe('2025-12-31');

    $agregat = $this->k->agregat([['pagu' => 100, 'realisasi' => 100], ['pagu' => 900, 'realisasi' => 0]]);
    expect($agregat)->toMatchArray(['pagu' => 1000, 'realisasi' => 100, 'sisa' => 900, 'serapan' => 10])
        ->and($agregat['status']->value)->toBe('aman');
});
