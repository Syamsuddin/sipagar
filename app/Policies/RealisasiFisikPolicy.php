<?php

namespace App\Policies;

use App\Models\SubKegiatan;
use App\Models\User;

/** Dipanggil dgn [RealisasiFisik::class, $subKegiatan]. */
class RealisasiFisikPolicy
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
