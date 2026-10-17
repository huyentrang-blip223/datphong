<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'guest';
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'points' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:local_products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
