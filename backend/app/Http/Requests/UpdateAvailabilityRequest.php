<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('room') && $this->user()?->can('update', $this->route('room'));
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'units_total' => ['required', 'integer', 'min:0', 'max:1000'],
            'units_held' => ['required', 'integer', 'min:0', 'max:1000'],
            'price_override' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', Rule::in(['open', 'closed'])],
        ];
    }
}
