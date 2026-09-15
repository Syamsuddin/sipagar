<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

/** S1: satu-satunya perubahan lewat form = aktifkan (draft → aktif). Kunci/buka → S5. */
class UpdateTahunAnggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('tahun_anggaran')) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['required', 'in:aktif']];
    }
}
