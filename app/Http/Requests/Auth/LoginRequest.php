<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'regex:/^[a-z0-9._-]{3,50}$/i'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi',
            'username.regex' => 'Username tidak valid',
            'password.required' => 'Kata sandi wajib diisi',
        ];
    }

    /** Kunci throttle docs/21: 5/menit per username+IP. */
    public function throttleKey(): string
    {
        return mb_strtolower($this->string('username')).'|'.$this->ip();
    }
}
