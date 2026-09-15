<?php

namespace App\Policies;

use App\Models\User;

/** Matriks docs/05: CRUD Pengguna hanya Admin; tidak ada hapus permanen. */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    /** Admin tidak boleh menonaktifkan/mereset dirinya sendiri. */
    public function kelolaStatus(User $user, User $target): bool
    {
        return $user->isAdmin() && $user->id !== $target->id;
    }

    public function delete(User $user, User $target): bool
    {
        return false;
    }
}
