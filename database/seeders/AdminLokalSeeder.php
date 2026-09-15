<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun admin untuk lingkungan lokal/testing saja (produksi: `php artisan sipagar:buat-admin`).
 */
class AdminLokalSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Administrator', 'password' => 'Admin12345', 'role' => Role::Admin, 'is_active' => true],
        );
    }
}
