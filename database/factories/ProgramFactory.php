<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\TahunAnggaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Program> */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tahun_anggaran_id' => TahunAnggaran::factory()->aktif(),
            'kode' => fake()->unique()->numerify('#.##.##'),
            'nama' => 'Program '.fake()->words(3, true),
            'urutan' => 0,
        ];
    }
}
