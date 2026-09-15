<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetTriwulan extends Model
{
    use Auditable;

    protected $table = 'target_triwulan';

    protected $fillable = ['sub_kegiatan_id', 'triwulan', 'target_keuangan', 'target_fisik'];

    protected function casts(): array
    {
        return ['triwulan' => 'integer', 'target_keuangan' => 'integer', 'target_fisik' => 'decimal:2'];
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class);
    }
}
