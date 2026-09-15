<?php

namespace App\Queries;

use App\Models\TahunAnggaran;
use Carbon\Carbon;

/**
 * Filter laporan (P4 docs/06): tanpa tahun → tahun aktif; tanpa TW → TW berjalan (TW4 bila bukan tahun ini).
 * Rentang tanggal: TW = preset (1 Jan s.d. akhir TW); `dari`/`sampai` eksplisit menang (keputusan pemilik S4).
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
        public readonly bool $tanggalEksplisit = false,
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

        $presetDari = Carbon::create($tahun->tahun, 1, 1);
        $presetSampai = Carbon::create($tahun->tahun, $tw * 3, 1)->endOfMonth()->endOfDay();
        $dari = ! empty($input['dari']) ? Carbon::parse($input['dari'])->startOfDay() : $presetDari;
        $sampai = ! empty($input['sampai']) ? Carbon::parse($input['sampai'])->endOfDay() : $presetSampai;
        // eksplisit hanya bila berbeda dari preset TW (form mengirim ulang nilai preset)
        $eksplisit = ! $dari->equalTo($presetDari) || ! $sampai->equalTo($presetSampai);

        return new self(
            $tahun, $tw,
            ! empty($input['bidang']) ? (int) $input['bidang'] : null,
            ! empty($input['sumber_dana']) ? (int) $input['sumber_dana'] : null,
            ! empty($input['sub_kegiatan']) ? (int) $input['sub_kegiatan'] : null,
            $dari, $sampai, $eksplisit,
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

    /** Periode utk nama berkas & judul: TWn bila rentang = preset TW, selain itu tanggal. */
    public function periodeRentang(): string
    {
        return $this->tanggalEksplisit ? $this->dari->toDateString().'_'.$this->sampai->toDateString() : $this->periodeTw();
    }

    public function labelRentang(): string
    {
        return $this->dari->format('d/m/Y').' s.d. '.$this->sampai->format('d/m/Y');
    }
}
