<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'guest';
    }

    public function rules(): array
    {
        return ['product_id' => ['required', 'integer', 'exists:local_products,id'], 'quantity' => ['required', 'integer', 'min:0', 'max:1000']];
    }
}
