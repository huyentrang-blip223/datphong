<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['current_password' => ['required', 'current_password:web'], 'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed', 'different:current_password']];
    }
}
