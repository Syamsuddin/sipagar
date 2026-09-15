<?php

namespace App\Policies;

use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\User;

/** docs/05: baca semua peran; tulis Admin semua / Operator bidangnya. create menerima SubKegiatan tujuan. */
class RealisasiKeuanganPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RealisasiKeuangan $r): bool
    {
        return true;
    }

    public function create(User $user, SubKegiatan $sk): bool
    {
        return $sk->milikBidangUser($user);
    }

    public function update(User $user, RealisasiKeuangan $r): bool
    {
        return $r->subKegiatan->milikBidangUser($user);
    }

    public function delete(User $user, RealisasiKeuangan $r): bool
    {
        return $r->subKegiatan->milikBidangUser($user);
    }
}
