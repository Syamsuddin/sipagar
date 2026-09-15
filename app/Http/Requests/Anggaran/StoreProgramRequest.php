<?php

namespace App\Http\Requests\Anggaran;

use App\Models\SubKegiatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SubKegiatan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'tahun_anggaran_id' => ['required', 'integer', Rule::exists('tahun_anggaran', 'id')],
            'kode' => ['required', 'string', 'max:20', 'regex:/^[0-9.]+$/',
                Rule::unique('program', 'kode')->where('tahun_anggaran_id', $this->input('tahun_anggaran_id'))->whereNull('deleted_at')],
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
