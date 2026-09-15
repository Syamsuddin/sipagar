<?php

namespace App\Models\Concerns;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer created/updated/deleted/restored → audit_logs (docs/08, docs/15). Model ✎ docs/07.
 * password & remember_token tidak pernah masuk (docs/21). Aksi khusus (login, lock_tahun, reset_password)
 * dicatat eksplisit oleh Service lewat AuditService::catat().
 */
trait Auditable
{
    private static bool $auditNonaktif = false;

    /** Kolom yang tidak dicatat: rahasia + stempel waktu. */
    protected function kolomTanpaAudit(): array
    {
        return ['password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'];
    }

    public static function bootAuditable(): void
    {
        static::created(fn (Model $m) => $m->catatAudit('created', null, $m->nilaiAudit($m->getAttributes())));
        static::updated(function (Model $m) {
            $baru = $m->nilaiAudit($m->getChanges());
            if ($baru === []) {
                return;
            }
            $lama = $m->nilaiAudit(array_intersect_key($m->getOriginal(), $baru));
            $m->catatAudit('updated', $lama, $baru);
        });
        static::deleted(fn (Model $m) => $m->catatAudit('deleted', $m->nilaiAudit($m->getAttributes()), null));
        if (method_exists(static::class, 'restored')) {
            static::restored(fn (Model $m) => $m->catatAudit('restored', null, $m->nilaiAudit($m->getAttributes())));
        }
    }

    /** Jalankan tanpa audit otomatis (Service mencatat aksi khusus sendiri). */
    public static function tanpaAudit(callable $aksi): mixed
    {
        self::$auditNonaktif = true;
        try {
            return $aksi();
        } finally {
            self::$auditNonaktif = false;
        }
    }

    /** @param array<string, mixed> $nilai */
    protected function nilaiAudit(array $nilai): array
    {
        $hasil = array_diff_key($nilai, array_flip($this->kolomTanpaAudit()));
        foreach ($hasil as $k => $v) {
            if ($v instanceof \BackedEnum) {
                $hasil[$k] = $v->value;
            } elseif ($v instanceof \DateTimeInterface) {
                $hasil[$k] = $v->format('Y-m-d H:i:s');
            }
        }

        return $hasil;
    }

    protected function catatAudit(string $action, ?array $old, ?array $new): void
    {
        if (self::$auditNonaktif) {
            return;
        }
        app(AuditService::class)->catat($action, $this, $old, $new);
    }
}
