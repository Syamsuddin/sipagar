<?php

namespace App\Models;

use App\Enums\StatusTahun;
use App\Models\Concerns\Auditable;
use Database\Factories\TahunAnggaranFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAnggaran extends Model
{
    /** @use HasFactory<TahunAnggaranFactory> */
    use Auditable, HasFactory;

    protected $table = 'tahun_anggaran';

    protected $fillable = ['tahun', 'status', 'locked_at', 'locked_by'];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'status' => StatusTahun::class,
            'locked_at' => 'datetime',
        ];
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function program(): HasMany
    {
        return $this->hasMany(Program::class)->orderBy('urutan')->orderBy('kode');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', StatusTahun::Aktif);
    }

    public function isTerkunci(): bool
    {
        return $this->status === StatusTahun::Terkunci;
    }
}
