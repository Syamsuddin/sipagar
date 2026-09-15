<?php

namespace App\Queries;

use App\Models\TahunAnggaran;
use Carbon\Carbon;

/**
 * Filter laporan (P4 docs/06): tanpa tahun → tahun aktif; tanpa TW → TW berjalan (TW4 bila bukan tahun ini).
 */
final class LaporanFilter
{
    public function __construct(
        public readonly TahunAnggaran $tahun,
        public readonly int $triwulan = 4,
        public readonly ?int $bidangId = null,
        public readonly ?int $sumberDanaId = null,
        public readonly ?int $subKegiatanId = null,
        public readonly ?Carbon $dari = null,
        public readonly ?Carbon $sampai = null,
    ) {}

    /** @param array<string, mixed> $input */
    public static function dari(array $input): ?self
    {
        $tahun = isset($input['tahun']) && is_numeric($input['tahun'])
            ? TahunAnggaran::where('tahun', (int) $input['tahun'])->first()
            : (TahunAnggaran::aktif()->first() ?? TahunAnggaran::orderByDesc('tahun')->first());
        if (! $tahun) {
            return null;
        }

        $tw = isset($input['triwulan']) && in_array((int) $input['triwulan'], [1, 2, 3, 4], true)
            ? (int) $input['triwulan'] : self::triwulanBerjalan($tahun->tahun);

        $dari = ! empty($input['dari']) ? Carbon::parse($input['dari'])->startOfDay() : Carbon::create($tahun->tahun, 1, 1);
        $sampai = ! empty($input['sampai']) ? Carbon::parse($input['sampai'])->endOfDay() : Carbon::create($tahun->tahun, 12, 31)->endOfDay();

        return new self(
            $tahun, $tw,
            ! empty($input['bidang']) ? (int) $input['bidang'] : null,
            ! empty($input['sumber_dana']) ? (int) $input['sumber_dana'] : null,
            ! empty($input['sub_kegiatan']) ? (int) $input['sub_kegiatan'] : null,
            $dari, $sampai,
        );
    }

    public static function triwulanBerjalan(int $tahun): int
    {
        $kini = now();

        return $kini->year === $tahun ? (int) ceil($kini->month / 3) : 4;
    }

    public function periodeTw(): string
    {
        return 'TW'.$this->triwulan;
    }
}
