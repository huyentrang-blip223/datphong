<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['host', 'seller'], true);
    }

    public function rules(): array
    {
        return ['confirmed' => ['required', 'accepted'], 'amount' => ['prohibited'], 'status' => ['prohibited']];
    }

    public function messages(): array
    {
        return ['confirmed.required' => 'Vui lòng xác nhận đã thực nhận hoặc trả lại tiền mặt.', 'confirmed.accepted' => 'Vui lòng xác nhận đã thực nhận hoặc trả lại tiền mặt.'];
    }
}
