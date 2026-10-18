<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'guest';
    }

    public function rules(): array
    {
        return ['amount' => ['prohibited'], 'status' => ['prohibited'], 'result' => ['prohibited'], 'paid_at' => ['prohibited']];
    }
}
