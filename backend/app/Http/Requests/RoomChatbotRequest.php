<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class RoomChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:3', 'max:800'],
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
            'location' => ['nullable', 'string', 'max:100'],
            'checkin' => ['nullable', 'required_with:checkout', 'date_format:Y-m-d', 'after_or_equal:today'],
            'checkout' => ['nullable', 'required_with:checkin', 'date_format:Y-m-d', 'after:checkin'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Hãy mô tả mong muốn của bạn.',
            'message.max' => 'Mong muốn tối đa 800 ký tự.',
            'guests.required' => 'Hãy nhập số người.',
            'guests.max' => 'Mỗi lần tìm tối đa 20 khách.',
            'checkin.required_with' => 'Hãy nhập đủ ngày nhận và trả phòng.',
            'checkout.required_with' => 'Hãy nhập đủ ngày nhận và trả phòng.',
            'checkin.after_or_equal' => 'Ngày nhận phòng không được ở quá khứ.',
            'checkout.after' => 'Ngày trả phòng phải sau ngày nhận phòng.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty() || ! $this->filled(['checkin', 'checkout'])) {
                return;
            }
            if (CarbonImmutable::parse($this->input('checkin'))->diffInDays(CarbonImmutable::parse($this->input('checkout'))) > 90) {
                $validator->errors()->add('checkout', 'Mỗi lần tìm kiếm tối đa 90 đêm.');
            }
        });
    }
}
