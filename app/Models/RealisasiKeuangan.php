<?php

namespace App\Models;

use Database\Factories\RealisasiKeuanganFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealisasiKeuangan extends Model
{
    /** @use HasFactory<RealisasiKeuanganFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'realisasi_keuangan';

    protected $fillable = ['sub_kegiatan_id', 'tanggal', 'jumlah', 'uraian', 'no_sp2d', 'lampiran_path', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'jumlah' => 'integer'];
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tahunAnggaran(): TahunAnggaran
    {
        return $this->subKegiatan->tahunAnggaran();
    }
}
