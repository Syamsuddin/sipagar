<?php

namespace App\Http\Requests\Anggaran;

use App\Models\SubKegiatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SubKegiatan::class) ?? false;
    }

    public function rules(): array
    {
        $model = $this->route('kegiatan');

        return [
            'kode' => ['required', 'string', 'max:20', 'regex:/^[0-9.]+$/',
                Rule::unique('kegiatan', 'kode')->where('program_id', $model->program_id)->whereNull('deleted_at')->ignore($model)],
            'nama' => ['required', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return (new StoreKegiatanRequest)->messages();
    }
}
