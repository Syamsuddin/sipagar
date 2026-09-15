<?php

namespace App\Http\Requests\Anggaran;

use App\Models\SubKegiatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SubKegiatan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'program_id' => ['required', 'integer', Rule::exists('program', 'id')],
            'kode' => ['required', 'string', 'max:20', 'regex:/^[0-9.]+$/',
                Rule::unique('kegiatan', 'kode')->where('program_id', $this->input('program_id'))->whereNull('deleted_at')],
            'nama' => ['required', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode wajib diisi',
            'kode.regex' => 'Kode hanya angka dan titik (format Kepmendagri)',
            'kode.unique' => 'Kode sudah dipakai pada tingkat & induk yang sama',
            'nama.required' => 'Nama wajib diisi',
        ];
    }
}
