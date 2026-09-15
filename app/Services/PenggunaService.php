<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * P6 docs/06: tambah, ubah, aktif/nonaktif, reset sandi. Hapus = nonaktifkan.
 * created/updated dicatat otomatis oleh trait Auditable; reset_password dicatat eksplisit.
 */
class PenggunaService
{
    public function __construct(private readonly AuditService $audit) {}

    /** @param array{name: string, username: string, password: string, role: string, bidang_id?: int|null} $data */
    public function tambah(array $data): User
    {
        $this->pastikanBidangOperator($data);

        $user = User::create([
            'name' => $data['name'],
            'username' => mb_strtolower($data['username']),
            'password' => $data['password'],
            'role' => $data['role'],
            'bidang_id' => $data['role'] === Role::Operator->value ? $data['bidang_id'] : null,
            'is_active' => true,
        ]);

        return $user;
    }

    /** @param array{name: string, username: string, role: string, bidang_id?: int|null} $data */
    public function ubah(User $user, array $data): User
    {
        $this->pastikanBidangOperator($data);

        $user->fill([
            'name' => $data['name'],
            'username' => mb_strtolower($data['username']),
            'role' => $data['role'],
            'bidang_id' => $data['role'] === Role::Operator->value ? $data['bidang_id'] : null,
        ])->save();

        return $user;
    }

    /**
     * Nonaktifkan = putus semua sesi aktif user itu (docs/05, docs/21).
     */
    public function setAktif(User $user, bool $aktif): User
    {
        $user->update(['is_active' => $aktif]);

        if (! $aktif) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return $user;
    }

    public function resetSandi(User $user, string $sandiBaru): User
    {
        User::tanpaAudit(fn () => $user->update(['password' => $sandiBaru]));
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $this->audit->catat('reset_password', $user);

        return $user;
    }

    /** @param array<string, mixed> $data */
    private function pastikanBidangOperator(array $data): void
    {
        if ($data['role'] === Role::Operator->value && empty($data['bidang_id'])) {
            throw ValidationException::withMessages(['bidang_id' => 'Bidang wajib untuk Operator']);
        }
    }
}
