<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MockPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'guest';
    }

    public function rules(): array
    {
        return [
            'result' => ['required', Rule::in(['success', 'fail'])],
        ];
    }
}
