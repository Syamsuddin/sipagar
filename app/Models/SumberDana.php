<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\SumberDanaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberDana extends Model
{
    /** @use HasFactory<SumberDanaFactory> */
    use Auditable, HasFactory;

    protected $table = 'sumber_dana';

    protected $fillable = ['kode', 'nama', 'css_class', 'urutan', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
