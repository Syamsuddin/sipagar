<?php

namespace App\Http\Requests\Realisasi;

use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;

class UpdateRealisasiKeuanganRequest extends StoreRealisasiKeuanganRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->realisasi()) ?? false;
    }

    public function rules(): array
    {
        return array_diff_key(parent::rules(), ['sub_kegiatan_id' => true]);
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge(['sub_kegiatan_id' => $this->realisasi()->sub_kegiatan_id]);
    }

    public function subKegiatan(): SubKegiatan
    {
        return $this->realisasi()->subKegiatan;
    }

    protected function jumlahLama(): int
    {
        return $this->realisasi()->jumlah;
    }

    public function realisasi(): RealisasiKeuangan
    {
        return $this->route('realisasiKeuangan');
    }
}
