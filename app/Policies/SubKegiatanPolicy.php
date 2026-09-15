<?php

namespace App\Policies;

use App\Models\SubKegiatan;
use App\Models\User;

/** docs/05: struktur anggaran dibaca semua peran, ditulis hanya Admin. Dipakai juga untuk Program/Kegiatan. */
class SubKegiatanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SubKegiatan $sk): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, SubKegiatan $sk): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, SubKegiatan $sk): bool
    {
        return $user->isAdmin();
    }
}
