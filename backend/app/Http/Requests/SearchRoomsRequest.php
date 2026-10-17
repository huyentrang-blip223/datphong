<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRoomsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location' => ['nullable', 'string', 'max:100'],
            'checkin' => ['required', 'date'],
            'checkout' => ['required', 'date', 'after:checkin'],
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', 'in:price_asc,price_desc,newest'],
            'room_type' => ['nullable', 'in:private_room,family_room,dorm,bungalow,whole_house'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
        ];
    }
}
