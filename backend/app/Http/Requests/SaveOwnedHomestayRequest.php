<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOwnedHomestayRequest extends FormRequest
{
    public function authorize(): bool
    {
        $homestay = $this->route('homestay');

        return $this->user()?->role === 'host' && (! $homestay || $this->user()->can('update', $homestay));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'alpha_dash', 'max:200', Rule::unique('homestays')->ignore($this->route('homestay')?->id)],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'checkin_time' => ['required', 'date_format:H:i'],
            'checkout_time' => ['required', 'date_format:H:i'],
            'document_type' => ['required', 'in:business_license,id_document,ownership_proof,other'],
            'document' => [$this->route('homestay') ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ];
    }
}
