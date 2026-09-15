<?php

namespace Database\Factories;

use App\Enums\StatusTahun;
use App\Models\TahunAnggaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TahunAnggaran>
 */
class TahunAnggaranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tahun' => fake()->unique()->numberBetween(2020, 2034),
            'status' => StatusTahun::Draft,
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn () => ['status' => StatusTahun::Aktif]);
    }

    public function terkunci(): static
    {
        return $this->state(fn () => ['status' => StatusTahun::Terkunci, 'locked_at' => now()]);
    }
}
