<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSumberDanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:30', Rule::unique('sumber_dana', 'kode')->ignore($this->route('sumber_dana'))],
            'nama' => ['required', 'string', 'max:100'],
            'css_class' => ['required', 'string', 'max:30', 'regex:/^sd-[a-z]+$/'],
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
