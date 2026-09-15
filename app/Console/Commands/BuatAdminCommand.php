<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/** `php artisan sipagar:buat-admin` (docs/11) — akun admin awal produksi. */
class BuatAdminCommand extends Command
{
    protected $signature = 'sipagar:buat-admin';

    protected $description = 'Buat akun Admin awal (prompt nama, username, sandi)';

    public function handle(): int
    {
        $name = (string) $this->ask('Nama tampil');
        $username = mb_strtolower((string) $this->ask('Username'));
        $password = (string) $this->secret('Kata sandi (min 8, huruf+angka)');

        $v = Validator::make(compact('name', 'username', 'password'), [
            'name' => ['required', 'max:100'],
            'username' => ['required', 'regex:/^[a-z0-9._-]{3,50}$/', 'unique:users,username'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
        ]);

        if ($v->fails()) {
            foreach ($v->errors()->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        User::create(['name' => $name, 'username' => $username, 'password' => $password, 'role' => Role::Admin, 'is_active' => true]);
        $this->info("Admin '{$username}' dibuat.");

        return self::SUCCESS;
    }
}
