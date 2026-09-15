<?php

namespace App\Models;

use App\Models\Concerns\ScopedByBidang;
use Database\Factories\SubKegiatanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubKegiatan extends Model
{
    /** @use HasFactory<SubKegiatanFactory> */
    use HasFactory, ScopedByBidang, SoftDeletes;

    protected $table = 'sub_kegiatan';

    protected $fillable = ['kegiatan_id', 'bidang_id', 'sumber_dana_id', 'kode', 'nama', 'pagu', 'pptk', 'urutan'];

    protected function casts(): array
    {
        return ['pagu' => 'integer'];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function targetTriwulan(): HasMany
    {
        return $this->hasMany(TargetTriwulan::class)->orderBy('triwulan');
    }

    public function realisasiKeuangan(): HasMany
    {
        return $this->hasMany(RealisasiKeuangan::class);
    }

    public function realisasiFisik(): HasMany
    {
        return $this->hasMany(RealisasiFisik::class)->orderBy('bulan');
    }

    /** Rantai sub_kegiatan → kegiatan → program → tahun (docs/16 #3). */
    public function tahunAnggaran(): TahunAnggaran
    {
        return $this->kegiatan->program->tahunAnggaran;
    }
}
