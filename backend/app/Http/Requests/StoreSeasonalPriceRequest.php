<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeasonalPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('room') && $this->user()?->can('update', $this->route('room'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price_per_night' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'priority' => ['required', 'integer', 'min:0', 'max:255'],
        ];
    }
}
