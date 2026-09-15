<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Pencatat audit bisnis (docs/15). S1: login/logout/reset_password;
 * trait Auditable untuk created/updated/deleted menyusul di S5 (F12).
 */
class AuditService
{
    /** Kolom yang tidak boleh masuk audit (docs/21). */
    private const RAHASIA = ['password', 'remember_token'];

    public function __construct(private readonly Request $request) {}

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public function catat(string $action, ?Model $auditable = null, ?array $old = null, ?array $new = null, ?int $userId = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $userId ?? $this->request->user()?->id,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $old ? $this->saring($old) : null,
            'new_values' => $new ? $this->saring($new) : null,
            'ip_address' => $this->request->ip(),
            'user_agent' => mb_substr((string) $this->request->userAgent(), 0, 255),
        ]);
    }

    /**
     * @param  array<string, mixed>  $nilai
     * @return array<string, mixed>
     */
    private function saring(array $nilai): array
    {
        return array_diff_key($nilai, array_flip(self::RAHASIA));
    }
}
