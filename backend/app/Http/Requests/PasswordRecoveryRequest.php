<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordRecoveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = ['email' => ['required', 'email', 'max:180']];
        if ($this->routeIs('password.update')) {
            $rules += ['token' => ['required', 'string', 'max:255'], 'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed']];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower((string) $this->email)]);
    }
}
