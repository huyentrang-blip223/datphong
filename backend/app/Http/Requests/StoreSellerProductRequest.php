<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSellerProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'seller'], true);
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:180'],
            'origin_place' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit' => ['required', 'string', 'max:40'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'stock_qty' => ['required', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(['draft', 'published', 'hidden'])],
            'image' => ['nullable', 'image', 'max:2048'],
        ];

        if ($this->user()?->role === 'admin') {
            $rules['seller_id'] = ['required', 'integer', Rule::exists('users', 'id')->where('role', 'seller')];
        }

        return $rules;
    }
}
