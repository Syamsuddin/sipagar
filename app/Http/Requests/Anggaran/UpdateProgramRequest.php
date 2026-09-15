<?php

namespace App\Http\Requests\Anggaran;

use App\Models\SubKegiatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SubKegiatan::class) ?? false;
    }

    public function rules(): array
    {
        $model = $this->route('program');

        return [
            'kode' => ['required', 'string', 'max:20', 'regex:/^[0-9.]+$/',
                Rule::unique('program', 'kode')->where('tahun_anggaran_id', $model->tahun_anggaran_id)->whereNull('deleted_at')->ignore($model)],
            'nama' => ['required', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return (new StoreProgramRequest)->messages();
    }
}
