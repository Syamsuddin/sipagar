<?php

namespace App\Http\Requests\Anggaran;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('subKegiatan')) ?? false;
    }

    public function rules(): array
    {
        $sk = $this->route('subKegiatan');

        return [
            'kode' => ['required', 'string', 'max:25', 'regex:/^[0-9.]+$/',
                Rule::unique('sub_kegiatan', 'kode')->where('kegiatan_id', $sk->kegiatan_id)->whereNull('deleted_at')->ignore($sk)],
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
        return (new StoreSubKegiatanRequest)->messages();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('pagu')) {
            $this->merge(['pagu' => parseRupiah($this->input('pagu'))]);
        }
    }
}
