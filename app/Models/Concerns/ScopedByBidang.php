<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Batas bidang (docs/05, docs/08): Operator hanya bidangnya; Admin/Pimpinan semua.
 * Scope lokal, bukan global — semua peran boleh MELIHAT struktur semua bidang (docs/05);
 * batas dipakai pada select Target dan di Policy tulis.
 */
trait ScopedByBidang
{
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isOperator()) {
            return $query->where($this->getTable().'.bidang_id', $user->bidang_id);
        }

        return $query;
    }

    public function milikBidangUser(User $user): bool
    {
        return $user->isAdmin() || ($user->isOperator() && $user->bidang_id === $this->bidang_id);
    }
}
