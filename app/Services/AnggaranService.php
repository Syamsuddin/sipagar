<?php

namespace App\Services;

use App\Exceptions\TahunTerkunciException;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * P1 docs/06: struktur Program → Kegiatan → Sub Kegiatan. Kunci tahun dicek ulang di sini (docs/21).
 * Audit created/updated/deleted otomatis lewat trait Auditable.
 */
class AnggaranService
{
    public function __construct(private readonly SerapanCalculator $kalkulator) {}

    // ---- Program --------------------------------------------------------------

    /** @param array{kode: string, nama: string, urutan?: int} $data */
    public function tambahProgram(TahunAnggaran $ta, array $data): Program
    {
        $this->pastikanTerbuka($ta);

        $program = $ta->program()->create($data);

        return $program;
    }

    /** @param array{kode: string, nama: string, urutan?: int} $data */
    public function ubahProgram(Program $program, array $data): Program
    {
        $this->pastikanTerbuka($program->tahunAnggaran);

        $program->update($data);

        return $program;
    }

    public function hapusProgram(Program $program): void
    {
        $this->pastikanTerbuka($program->tahunAnggaran);

        if ($program->kegiatan()->exists()) {
            throw ValidationException::withMessages(['program' => 'Program masih memiliki kegiatan; hapus kegiatannya dulu']);
        }

        $program->delete();
    }

    // ---- Kegiatan -------------------------------------------------------------

    /** @param array{kode: string, nama: string, urutan?: int} $data */
    public function tambahKegiatan(Program $program, array $data): Kegiatan
    {
        $this->pastikanTerbuka($program->tahunAnggaran);

        $kegiatan = $program->kegiatan()->create($data);

        return $kegiatan;
    }

    /** @param array{kode: string, nama: string, urutan?: int} $data */
    public function ubahKegiatan(Kegiatan $kegiatan, array $data): Kegiatan
    {
        $this->pastikanTerbuka($kegiatan->tahunAnggaran());

        $kegiatan->update($data);

        return $kegiatan;
    }

    public function hapusKegiatan(Kegiatan $kegiatan): void
    {
        $this->pastikanTerbuka($kegiatan->tahunAnggaran());

        if ($kegiatan->subKegiatan()->exists()) {
            throw ValidationException::withMessages(['kegiatan' => 'Kegiatan masih memiliki sub kegiatan; hapus sub kegiatannya dulu']);
        }

        $kegiatan->delete();
    }

    // ---- Sub Kegiatan ---------------------------------------------------------

    /** @param array{kode: string, nama: string, pagu: int, bidang_id: int, sumber_dana_id: int, pptk?: string|null, urutan?: int} $data */
    public function tambahSubKegiatan(Kegiatan $kegiatan, array $data): SubKegiatan
    {
        $this->pastikanTerbuka($kegiatan->tahunAnggaran());

        $sk = $kegiatan->subKegiatan()->create($data);

        return $sk;
    }

    /**
     * Pagu tidak boleh < realisasi terkumpul (P1 docs/06).
     *
     * @param  array{kode: string, nama: string, pagu: int, bidang_id: int, sumber_dana_id: int, pptk?: string|null, urutan?: int}  $data
     */
    public function ubahSubKegiatan(SubKegiatan $sk, array $data): SubKegiatan
    {
        $this->pastikanTerbuka($sk->tahunAnggaran());

        return DB::transaction(function () use ($sk, $data) {
            $realisasi = $this->kalkulator->realisasi($sk);
            if ((int) $data['pagu'] < $realisasi) {
                throw ValidationException::withMessages(['pagu' => 'Pagu tidak boleh kurang dari realisasi '.rupiah($realisasi)]);
            }

            $sk->update($data);

            return $sk;
        });
    }

    /** Soft delete + audit; ditolak 422 selama ada realisasi. */
    public function hapusSubKegiatan(SubKegiatan $sk): void
    {
        $this->pastikanTerbuka($sk->tahunAnggaran());

        if ($sk->realisasiKeuangan()->exists()) {
            throw ValidationException::withMessages(['sub_kegiatan' => 'Sub kegiatan sudah memiliki realisasi; tidak dapat dihapus']);
        }

        $sk->delete();
    }

    private function pastikanTerbuka(TahunAnggaran $ta): void
    {
        if ($ta->isTerkunci()) {
            throw new TahunTerkunciException($ta);
        }
    }
}
