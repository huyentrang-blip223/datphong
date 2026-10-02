<?php
namespace App\Http\Requests;

use App\Models\Homestay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $homestay = Homestay::find($this->input('homestay_id'));

        return $homestay
            && ($this->user()?->role === 'admin'
                || ($this->user()?->role === 'host' && (int) $homestay->owner_id === (int) $this->user()->id));
    }

    public function rules(): array
    {
        return [
            'homestay_id' => ['required', 'integer', 'exists:homestays,id'],
            'name' => ['required', 'string', 'max:160'],
            'room_type' => ['required', Rule::in(['private_room', 'family_room', 'dorm', 'bungalow', 'whole_house'])],
            'max_guests' => ['required', 'integer', 'min:1', 'max:20'],
            'bed_count' => ['required', 'integer', 'min:1', 'max:20'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'active' => ['required', 'boolean'],
        ];
    }
}
