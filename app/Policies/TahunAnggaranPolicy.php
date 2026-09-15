<?php

namespace App\Policies;

use App\Models\TahunAnggaran;
use App\Models\User;

class TahunAnggaranPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, TahunAnggaran $ta): bool
    {
        return $user->isAdmin();
    }

    public function kunci(User $user, TahunAnggaran $ta): bool
    {
        return $user->isAdmin();
    }
}
