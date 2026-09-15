<?php

namespace App\Http\Requests\Pengguna;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetSandiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelolaStatus', $this->route('pengguna')) ?? false;
    }

    public function rules(): array
    {
        return ['password' => ['required', Password::min(8)->letters()->numbers()]];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Sandi baru wajib diisi',
            'password.min' => 'Kata sandi minimal 8 karakter',
            'password.letters' => 'Kata sandi harus memuat huruf',
            'password.numbers' => 'Kata sandi harus memuat angka',
        ];
    }
}
