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
