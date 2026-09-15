<?php

namespace Database\Factories;

use App\Models\Kegiatan;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Kegiatan> */
class KegiatanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'kode' => fake()->unique()->numerify('#.##.##.#.##'),
            'nama' => 'Kegiatan '.fake()->words(3, true),
            'urutan' => 0,
        ];
    }
}
