<?php

namespace Database\Factories;

use App\Models\SumberDana;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SumberDana>
 */
class SumberDanaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->regexify('[A-Z]{3,6}'),
            'nama' => fake()->words(3, true),
            'css_class' => 'sd-lainnya',
            'urutan' => 0,
            'is_active' => true,
        ];
    }
}
