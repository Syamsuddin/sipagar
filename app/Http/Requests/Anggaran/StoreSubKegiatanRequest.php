<?php

namespace App\Http\Requests\Anggaran;

use App\Models\SubKegiatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SubKegiatan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'kegiatan_id' => ['required', 'integer', Rule::exists('kegiatan', 'id')],
            'kode' => ['required', 'string', 'max:25', 'regex:/^[0-9.]+$/',
                Rule::unique('sub_kegiatan', 'kode')->where('kegiatan_id', $this->input('kegiatan_id'))->whereNull('deleted_at')],
            'nama' => ['required', 'string', 'max:255'],
            'pagu' => ['required', 'integer', 'min:1', 'max:9999999999999'],
            'bidang_id' => ['required', 'integer', Rule::exists('bidang', 'id')],
            'sumber_dana_id' => ['required', 'integer', Rule::exists('sumber_dana', 'id')],
            'pptk' => ['nullable', 'string', 'max:100'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode wajib diisi',
            'kode.regex' => 'Kode hanya angka dan titik (format Kepmendagri)',
            'kode.unique' => 'Kode sudah dipakai pada kegiatan yang sama',
            'nama.required' => 'Nama wajib diisi',
            'pagu.required' => 'Pagu wajib diisi',
            'pagu.integer' => 'Pagu harus angka bulat',
            'pagu.min' => 'Pagu harus lebih dari 0',
            'pagu.max' => 'Pagu melebihi batas 9.999.999.999.999',
            'bidang_id.required' => 'Bidang wajib dipilih',
            'sumber_dana_id.required' => 'Sumber dana wajib dipilih',
        ];
    }

    /** Terima "Rp 1.234.567" (docs/16 #6). */
    protected function prepareForValidation(): void
    {
        if ($this->has('pagu')) {
            $this->merge(['pagu' => parseRupiah($this->input('pagu'))]);
        }
    }
}
