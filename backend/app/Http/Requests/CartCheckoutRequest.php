<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'guest';
    }

    public function rules(): array
    {
        return ['checkout_key' => ['required', 'uuid'], 'points' => ['nullable', 'integer', 'min:0', 'max:1000000']];
    }
}
