<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['seller', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['confirmed', 'packing', 'delivered', 'cancelled'])],
        ];
    }
}
