<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['full_name' => ['required', 'string', 'max:160'], 'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users')->ignore($this->user()->id)],
            'current_password' => ['required', 'current_password:web']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower((string) $this->email)]);
    }
}
