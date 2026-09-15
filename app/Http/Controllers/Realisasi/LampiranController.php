<?php

namespace App\Http\Controllers\Realisasi;

use App\Http\Controllers\Controller;
use App\Models\RealisasiKeuangan;
use App\Services\RealisasiService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** GET /realisasi/lampiran/{id}: disk private, hanya lewat Policy view (docs/21). */
class LampiranController extends Controller
{
    public function show(RealisasiKeuangan $realisasiKeuangan): BinaryFileResponse
    {
        $this->authorize('view', $realisasiKeuangan);

        $disk = Storage::disk(RealisasiService::DISK_LAMPIRAN);
        abort_unless($realisasiKeuangan->lampiran_path && $disk->exists($realisasiKeuangan->lampiran_path), 404);

        return response()->file($disk->path($realisasiKeuangan->lampiran_path), [
            'Content-Disposition' => 'inline; filename="lampiran-'.$realisasiKeuangan->id.'.'.pathinfo($realisasiKeuangan->lampiran_path, PATHINFO_EXTENSION).'"',
        ]);
    }
}
