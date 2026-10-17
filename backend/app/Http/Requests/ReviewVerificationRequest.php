<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return ['status' => ['required', 'in:approved,rejected'], 'note' => ['nullable', 'required_if:status,rejected', 'string', 'max:255']];
    }
}
