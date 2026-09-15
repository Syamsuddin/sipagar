<?php

namespace Database\Factories;

use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RealisasiKeuangan> */
class RealisasiKeuanganFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sub_kegiatan_id' => SubKegiatan::factory(),
            'tanggal' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'jumlah' => 10_000_000,
            'uraian' => fake()->sentence(4),
            'no_sp2d' => fake()->numerify('SP2D-####'),
            'created_by' => User::factory()->admin(),
        ];
    }
}
