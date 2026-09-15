<?php

namespace App\Http\Middleware;

use App\Exceptions\TahunTerkunciException;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tahun terkunci → 423 pada semua route tulis anggaran/target/realisasi (docs/21).
 * Tahun diresolusi dari model route atau input induk (docs/16: rantai sub_kegiatan→kegiatan→program).
 */
class EnsureTahunTerbuka
{
    public function handle(Request $request, Closure $next): Response
    {
        $ta = $this->resolusiTahun($request);

        if ($ta?->isTerkunci()) {
            throw new TahunTerkunciException($ta);
        }

        return $next($request);
    }

    private function resolusiTahun(Request $request): ?TahunAnggaran
    {
        foreach (['subKegiatan', 'sub_kegiatan', 'kegiatan', 'program', 'tahunAnggaran', 'tahun_anggaran'] as $param) {
            $model = $request->route($param);
            if ($model instanceof SubKegiatan || $model instanceof Kegiatan) {
                return $model->tahunAnggaran();
            }
            if ($model instanceof Program) {
                return $model->tahunAnggaran;
            }
            if ($model instanceof TahunAnggaran) {
                return $model;
            }
        }

        if ($request->filled('sub_kegiatan_id')) {
            return SubKegiatan::find($request->integer('sub_kegiatan_id'))?->tahunAnggaran();
        }
        if ($request->filled('kegiatan_id')) {
            return Kegiatan::find($request->integer('kegiatan_id'))?->tahunAnggaran();
        }
        if ($request->filled('program_id')) {
            return Program::find($request->integer('program_id'))?->tahunAnggaran;
        }
        if ($request->filled('tahun_anggaran_id')) {
            return TahunAnggaran::find($request->integer('tahun_anggaran_id'));
        }

        return null;
    }
}
