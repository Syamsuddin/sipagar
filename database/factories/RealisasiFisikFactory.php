<?php

namespace Database\Factories;

use App\Models\RealisasiFisik;
use App\Models\SubKegiatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RealisasiFisik> */
class RealisasiFisikFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sub_kegiatan_id' => SubKegiatan::factory(),
            'bulan' => fake()->unique()->numberBetween(1, 12),
            'persen' => 10,
        ];
    }
}
