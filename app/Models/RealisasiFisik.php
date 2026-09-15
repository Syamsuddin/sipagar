<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\RealisasiFisikFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisasiFisik extends Model
{
    /** @use HasFactory<RealisasiFisikFactory> */
    use Auditable, HasFactory;

    protected $table = 'realisasi_fisik';

    protected $fillable = ['sub_kegiatan_id', 'bulan', 'persen', 'keterangan', 'updated_by'];

    protected function casts(): array
    {
        return ['bulan' => 'integer', 'persen' => 'decimal:2'];
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class);
    }
}
