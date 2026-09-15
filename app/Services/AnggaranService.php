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
 */
class AnggaranService
{
    public function __construct(private readonly AuditService $audit, private readonly SerapanCalculator $kalkulator) {}

    // ---- Program --------------------------------------------------------------

    /** @param array{kode: string, nama: string, urutan?: int} $data */
    public function tambahProgram(TahunAnggaran $ta, array $data): Program
    {
        $this->pastikanTerbuka($ta);

        $program = $ta->program()->create($data);
        $this->audit->catat('created', $program, null, $program->only(['kode', 'nama', 'tahun_anggaran_id']));

        return $program;
    }

    /** @param array{kode: string, nama: string, urutan?: int} $data */
    public function ubahProgram(Program $program, array $data): Program
    {
        $this->pastikanTerbuka($program->tahunAnggaran);

        $lama = $program->only(['kode', 'nama', 'urutan']);
        $program->update($data);
        $this->audit->catat('updated', $program, $lama, $program->only(['kode', 'nama', 'urutan']));

        return $program;
    }

    public function hapusProgram(Program $program): void
    {
        $this->pastikanTerbuka($program->tahunAnggaran);

        if ($program->kegiatan()->exists()) {
            throw ValidationException::withMessages(['program' => 'Program masih memiliki kegiatan; hapus kegiatannya dulu']);
        }

        $program->delete();
        $this->audit->catat('deleted', $program, $program->only(['kode', 'nama']));
    }

    // ---- Kegiatan -------------------------------------------------------------

    /** @param array{kode: string, nama: string, urutan?: int} $data */
    public function tambahKegiatan(Program $program, array $data): Kegiatan
    {
        $this->pastikanTerbuka($program->tahunAnggaran);

        $kegiatan = $program->kegiatan()->create($data);
        $this->audit->catat('created', $kegiatan, null, $kegiatan->only(['kode', 'nama', 'program_id']));

        return $kegiatan;
    }

    /** @param array{kode: string, nama: string, urutan?: int} $data */
    public function ubahKegiatan(Kegiatan $kegiatan, array $data): Kegiatan
    {
        $this->pastikanTerbuka($kegiatan->tahunAnggaran());

        $lama = $kegiatan->only(['kode', 'nama', 'urutan']);
        $kegiatan->update($data);
        $this->audit->catat('updated', $kegiatan, $lama, $kegiatan->only(['kode', 'nama', 'urutan']));

        return $kegiatan;
    }

    public function hapusKegiatan(Kegiatan $kegiatan): void
    {
        $this->pastikanTerbuka($kegiatan->tahunAnggaran());

        if ($kegiatan->subKegiatan()->exists()) {
            throw ValidationException::withMessages(['kegiatan' => 'Kegiatan masih memiliki sub kegiatan; hapus sub kegiatannya dulu']);
        }

        $kegiatan->delete();
        $this->audit->catat('deleted', $kegiatan, $kegiatan->only(['kode', 'nama']));
    }

    // ---- Sub Kegiatan ---------------------------------------------------------

    /** @param array{kode: string, nama: string, pagu: int, bidang_id: int, sumber_dana_id: int, pptk?: string|null, urutan?: int} $data */
    public function tambahSubKegiatan(Kegiatan $kegiatan, array $data): SubKegiatan
    {
        $this->pastikanTerbuka($kegiatan->tahunAnggaran());

        $sk = $kegiatan->subKegiatan()->create($data);
        $this->audit->catat('created', $sk, null, $sk->only(['kode', 'nama', 'pagu', 'bidang_id', 'sumber_dana_id', 'pptk']));

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

            $kolom = ['kode', 'nama', 'pagu', 'bidang_id', 'sumber_dana_id', 'pptk', 'urutan'];
            $lama = $sk->only($kolom);
            $sk->update($data);
            $this->audit->catat('updated', $sk, $lama, $sk->only($kolom));

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
        $this->audit->catat('deleted', $sk, $sk->only(['kode', 'nama', 'pagu']));
    }

    private function pastikanTerbuka(TahunAnggaran $ta): void
    {
        if ($ta->isTerkunci()) {
            throw new TahunTerkunciException($ta);
        }
    }
}
