<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExperienceBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'guest';
    }

    public function rules(): array
    {
        return [
            'people_count' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }
}
