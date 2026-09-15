<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\BidangFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bidang extends Model
{
    /** @use HasFactory<BidangFactory> */
    use Auditable, HasFactory;

    protected $table = 'bidang';

    protected $fillable = ['kode', 'nama', 'urutan', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
