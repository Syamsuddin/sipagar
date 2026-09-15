<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Bidang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->regexify('[a-z]{5,10}'),
            'password' => 'Sandi1234',
            'role' => Role::Pimpinan,
            'bidang_id' => null,
            'is_active' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => Role::Admin]);
    }

    public function operator(?Bidang $bidang = null): static
    {
        return $this->state(fn () => [
            'role' => Role::Operator,
            'bidang_id' => $bidang?->id ?? Bidang::factory(),
        ]);
    }

    public function pimpinan(): static
    {
        return $this->state(fn () => ['role' => Role::Pimpinan]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
