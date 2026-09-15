<?php

namespace App\Services;

use App\Exceptions\MelebihiSisaPaguException;
use App\Exceptions\TahunTerkunciException;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * P3 docs/06. Sisa pagu dicek ulang di transaksi (docs/21) walau FormRequest sudah memvalidasi.
 * Lampiran: disk `private`, nama `lampiran/{tahun}/{id}.{ext}` (docs/07, docs/21).
 */
class RealisasiService
{
    public const DISK_LAMPIRAN = 'private';

    public function __construct(private readonly AuditService $audit, private readonly SerapanCalculator $kalkulator) {}

    /** @param array{tanggal: string, jumlah: int, uraian: string, no_sp2d?: string|null} $data */
    public function catat(SubKegiatan $sk, array $data, User $oleh, ?UploadedFile $lampiran = null): RealisasiKeuangan
    {
        $this->pastikanTerbuka($sk);

        return DB::transaction(function () use ($sk, $data, $oleh, $lampiran) {
            $sk = SubKegiatan::lockForUpdate()->findOrFail($sk->id);
            $sisa = $this->kalkulator->sisa($sk->pagu, $this->kalkulator->realisasi($sk));
            if ((int) $data['jumlah'] > $sisa) {
                throw new MelebihiSisaPaguException($sisa);
            }

            $r = $sk->realisasiKeuangan()->create([
                'tanggal' => $data['tanggal'],
                'jumlah' => (int) $data['jumlah'],
                'uraian' => $data['uraian'],
                'no_sp2d' => $data['no_sp2d'] ?? null,
                'created_by' => $oleh->id,
            ]);

            if ($lampiran) {
                $r->update(['lampiran_path' => $this->simpanLampiran($r, $lampiran)]);
            }

            $this->audit->catat('created', $r, null, $r->only(['sub_kegiatan_id', 'tanggal', 'jumlah', 'uraian', 'no_sp2d', 'lampiran_path']));

            return $r;
        });
    }

    /** @param array{tanggal: string, jumlah: int, uraian: string, no_sp2d?: string|null} $data */
    public function ubah(RealisasiKeuangan $r, array $data, User $oleh, ?UploadedFile $lampiran = null): RealisasiKeuangan
    {
        $this->pastikanTerbuka($r->subKegiatan);

        return DB::transaction(function () use ($r, $data, $oleh, $lampiran) {
            $sk = SubKegiatan::lockForUpdate()->findOrFail($r->sub_kegiatan_id);
            $sisaTanpaIni = $this->kalkulator->sisa($sk->pagu, $this->kalkulator->realisasi($sk) - $r->jumlah);
            if ((int) $data['jumlah'] > $sisaTanpaIni) {
                throw new MelebihiSisaPaguException($sisaTanpaIni);
            }

            $kolom = ['tanggal', 'jumlah', 'uraian', 'no_sp2d', 'lampiran_path'];
            $lama = $r->only($kolom);
            $r->fill([
                'tanggal' => $data['tanggal'],
                'jumlah' => (int) $data['jumlah'],
                'uraian' => $data['uraian'],
                'no_sp2d' => $data['no_sp2d'] ?? null,
                'updated_by' => $oleh->id,
            ]);
            if ($lampiran) {
                $this->hapusLampiran($r);
                $r->lampiran_path = $this->simpanLampiran($r, $lampiran);
            }
            $r->save();

            $this->audit->catat('updated', $r, $lama, $r->only($kolom));

            return $r;
        });
    }

    /** Soft delete + audit; berkas lampiran dibiarkan (dipulihkan bila restore). */
    public function hapus(RealisasiKeuangan $r): void
    {
        $this->pastikanTerbuka($r->subKegiatan);

        $r->delete();
        $this->audit->catat('deleted', $r, $r->only(['sub_kegiatan_id', 'tanggal', 'jumlah', 'uraian']));
    }

    /**
     * F06: upsert bulan terisi, hapus bulan yang dikosongkan. Monoton divalidasi di FormRequest.
     *
     * @param  array<int, float|null>  $persenPerBulan  bulan => persen|null
     */
    public function simpanFisik(SubKegiatan $sk, array $persenPerBulan, User $oleh): void
    {
        $this->pastikanTerbuka($sk);

        DB::transaction(function () use ($sk, $persenPerBulan, $oleh) {
            $lama = $this->kalkulator->fisikPerBulan($sk);

            foreach ($persenPerBulan as $bulan => $persen) {
                if ($persen === null || $persen === '') {
                    $sk->realisasiFisik()->where('bulan', $bulan)->delete();

                    continue;
                }
                $sk->realisasiFisik()->updateOrCreate(['bulan' => (int) $bulan], ['persen' => round((float) $persen, 2), 'updated_by' => $oleh->id]);
            }

            $this->audit->catat('updated', $sk, ['fisik' => $lama], ['fisik' => $this->kalkulator->fisikPerBulan($sk)]);
        });
    }

    private function simpanLampiran(RealisasiKeuangan $r, UploadedFile $file): string
    {
        $tahun = $r->subKegiatan->tahunAnggaran()->tahun;
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $path = "lampiran/{$tahun}/{$r->id}.{$ext}";
        Storage::disk(self::DISK_LAMPIRAN)->putFileAs("lampiran/{$tahun}", $file, "{$r->id}.{$ext}");

        return $path;
    }

    private function hapusLampiran(RealisasiKeuangan $r): void
    {
        if ($r->lampiran_path) {
            Storage::disk(self::DISK_LAMPIRAN)->delete($r->lampiran_path);
        }
    }

    private function pastikanTerbuka(SubKegiatan $sk): void
    {
        $ta = $sk->tahunAnggaran();
        if ($ta->isTerkunci()) {
            throw new TahunTerkunciException($ta);
        }
    }
}
