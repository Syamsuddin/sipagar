<?php

namespace App\Http\Requests\Master;

use App\Models\TahunAnggaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTahunAnggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TahunAnggaran::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'tahun' => [
                'required', 'integer',
                'min:'.config('sipagar.tahun_min'), 'max:'.config('sipagar.tahun_max'),
                Rule::unique('tahun_anggaran', 'tahun'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun.required' => 'Tahun wajib diisi',
            'tahun.min' => 'Tahun minimal '.config('sipagar.tahun_min'),
            'tahun.max' => 'Tahun maksimal '.config('sipagar.tahun_max'),
            'tahun.unique' => 'Tahun anggaran sudah ada',
        ];
    }
}
