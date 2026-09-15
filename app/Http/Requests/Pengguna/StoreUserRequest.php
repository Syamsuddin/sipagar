<?php

namespace App\Http\Requests\Pengguna;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'regex:/^[a-z0-9._-]{3,50}$/', Rule::unique('users', 'username')],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::enum(Role::class)],
            'bidang_id' => ['nullable', 'integer', Rule::exists('bidang', 'id'), Rule::requiredIf(fn () => $this->input('role') === Role::Operator->value)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.regex' => 'Username: huruf kecil, angka, titik, garis (3–50)',
            'username.unique' => 'Username sudah dipakai',
            'password.required' => 'Sandi awal wajib diisi',
            'password.min' => 'Kata sandi minimal 8 karakter',
            'password.letters' => 'Kata sandi harus memuat huruf',
            'password.numbers' => 'Kata sandi harus memuat angka',
            'role.required' => 'Peran wajib dipilih',
            'bidang_id.required' => 'Bidang wajib untuk Operator',
            'bidang_id.exists' => 'Bidang tidak ditemukan',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['username' => mb_strtolower(trim((string) $this->input('username')))]);
    }
}
