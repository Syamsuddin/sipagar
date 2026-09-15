<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengaturanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'kop_nama_instansi' => ['required', 'string', 'max:150'],
            'kop_alamat' => ['nullable', 'string', 'max:500'],
            'ttd_nama' => ['nullable', 'string', 'max:100'],
            'ttd_nip' => ['nullable', 'string', 'max:30'],
            'ttd_jabatan' => ['nullable', 'string', 'max:100'],
            'ttd_kota' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return ['kop_nama_instansi.required' => 'Nama instansi wajib diisi'];
    }
}
