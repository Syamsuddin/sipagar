<?php

namespace App\Http\Requests\Pengguna;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('pengguna')) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'regex:/^[a-z0-9._-]{3,50}$/', Rule::unique('users', 'username')->ignore($this->route('pengguna'))],
            'role' => ['required', Rule::enum(Role::class)],
            'bidang_id' => ['nullable', 'integer', Rule::exists('bidang', 'id'), Rule::requiredIf(fn () => $this->input('role') === Role::Operator->value)],
        ];
    }

    public function messages(): array
    {
        return (new StoreUserRequest)->messages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['username' => mb_strtolower(trim((string) $this->input('username')))]);
    }
}
