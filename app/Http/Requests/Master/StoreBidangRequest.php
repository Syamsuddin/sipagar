<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBidangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:10', Rule::unique('bidang', 'kode')],
            'nama' => ['required', 'string', 'max:150'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode wajib diisi',
            'kode.unique' => 'Kode sudah dipakai',
            'nama.required' => 'Nama wajib diisi',
            'css_class.regex' => 'Kelas badge harus sd-*',
        ];
    }
}
