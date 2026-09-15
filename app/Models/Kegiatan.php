<?php

namespace App\Models;

use Database\Factories\KegiatanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kegiatan extends Model
{
    /** @use HasFactory<KegiatanFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'kegiatan';

    protected $fillable = ['program_id', 'kode', 'nama', 'urutan'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function subKegiatan(): HasMany
    {
        return $this->hasMany(SubKegiatan::class)->orderBy('urutan')->orderBy('kode');
    }

    public function tahunAnggaran(): TahunAnggaran
    {
        return $this->program->tahunAnggaran;
    }
}
