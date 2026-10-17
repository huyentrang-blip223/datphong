<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower((string) $this->email)]);
    }

    public function rules(): array
    {
        return ['full_name' => ['required', 'string', 'max:160'], 'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users')->ignore($this->route('user')?->id)],
            'role' => ['required', 'in:admin,host,seller,guest'], 'status' => ['required', 'in:active,locked'],
            'password' => [$this->route('user') ? 'nullable' : 'required', 'string', 'min:8', 'max:128', 'confirmed']];
    }
}
