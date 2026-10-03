<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSellerProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'seller';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'origin_place' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit' => ['required', 'string', 'max:40'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'stock_qty' => ['required', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(['draft', 'published', 'hidden'])],
        ];
    }
}
