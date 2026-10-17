<?php
namespace App\Http\Requests;

use App\Models\Homestay;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $experience = $this->route('experience');
        $homestayId = (int) $this->input('homestay_id');

        return $experience
            && Homestay::where('id', $experience->homestay_id)->where('owner_id', $this->user()?->id)->exists()
            && Homestay::where('id', $homestayId)->where('owner_id', $this->user()?->id)->exists();
    }

    public function rules(): array
    {
        return [
            'homestay_id' => ['required', 'integer', 'exists:homestays,id'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', 'in:draft,open,full,cancelled'],
        ];
    }
}
