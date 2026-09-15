<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const KUNCI = [
        'kop_nama_instansi', 'kop_alamat', 'kop_logo_path',
        'ttd_nama', 'ttd_nip', 'ttd_jabatan', 'ttd_kota',
    ];

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    /** @return array<string, string|null> key => value untuk seluruh KUNCI */
    public static function semua(): array
    {
        $tersimpan = static::query()->pluck('value', 'key')->all();

        return array_merge(array_fill_keys(self::KUNCI, null), $tersimpan);
    }

    public static function nilai(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }
}
