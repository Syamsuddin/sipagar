<?php

namespace Database\Factories;

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SubKegiatan> — state docs/13: untukBidang(), pagu(), tahun() */
class SubKegiatanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kegiatan_id' => Kegiatan::factory(),
            'bidang_id' => Bidang::factory(),
            'sumber_dana_id' => SumberDana::factory(),
            'kode' => fake()->unique()->numerify('#.##.##.#.##.####'),
            'nama' => 'Sub Kegiatan '.fake()->words(3, true),
            'pagu' => 100_000_000,
            'pptk' => fake()->name(),
            'urutan' => 0,
        ];
    }

    public function untukBidang(Bidang $bidang): static
    {
        return $this->state(fn () => ['bidang_id' => $bidang->id]);
    }

    public function pagu(int $pagu): static
    {
        return $this->state(fn () => ['pagu' => $pagu]);
    }

    public function tahun(TahunAnggaran $ta): static
    {
        return $this->state(fn () => ['kegiatan_id' => Kegiatan::factory()->for(Program::factory()->for($ta, 'tahunAnggaran'))]);
    }
}
