<?php

namespace Database\Factories;

use App\Models\Bidang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bidang>
 */
class BidangFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->regexify('[A-Z]{2,4}'),
            'nama' => 'Bidang '.fake()->words(2, true),
            'urutan' => 0,
            'is_active' => true,
        ];
    }
}
