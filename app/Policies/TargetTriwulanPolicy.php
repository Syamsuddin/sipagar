<?php

namespace App\Policies;

use App\Models\SubKegiatan;
use App\Models\User;

/** docs/05: Admin semua bidang; Operator hanya Sub Kegiatan bidangnya; Pimpinan baca saja. */
class TargetTriwulanPolicy
{
    public function view(User $user, SubKegiatan $sk): bool
    {
        return true;
    }

    public function update(User $user, SubKegiatan $sk): bool
    {
        return $sk->milikBidangUser($user);
    }
}
