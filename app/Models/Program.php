<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'program';

    protected $fillable = ['tahun_anggaran_id', 'kode', 'nama', 'urutan'];

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function kegiatan(): HasMany
    {
        return $this->hasMany(Kegiatan::class)->orderBy('urutan')->orderBy('kode');
    }
}
