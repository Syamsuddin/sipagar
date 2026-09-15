<?php

namespace App\Http\Requests\Profil;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UbahSandiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'sandi_lama' => ['required', 'current_password'],
            'sandi_baru' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'sandi_lama.required' => 'Kata sandi lama wajib diisi',
            'sandi_lama.current_password' => 'Kata sandi lama salah',
            'sandi_baru.required' => 'Kata sandi baru wajib diisi',
            'sandi_baru.confirmed' => 'Konfirmasi kata sandi tidak cocok',
            'sandi_baru.min' => 'Kata sandi minimal 8 karakter',
            'sandi_baru.letters' => 'Kata sandi harus memuat huruf',
            'sandi_baru.numbers' => 'Kata sandi harus memuat angka',
        ];
    }
}
