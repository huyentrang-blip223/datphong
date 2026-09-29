<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomestayRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }

    public function rules(): array
    {
        $id = $this->route('homestay')?->id ?? $this->route('homestay');
        return [
            'owner_id' => ['required','integer',Rule::exists('users','id')->where(fn($q) => $q->where('role','host'))],
            'name' => ['required','string','max:180'],
            'slug' => ['required','alpha_dash','max:200',Rule::unique('homestays','slug')->ignore($id)],
            'province' => ['required','string','max:100'],
            'district' => ['nullable','string','max:100'],
            'address_line' => ['required','string','max:255'],
            'description' => ['nullable','string','max:3000'],
            'checkin_time' => ['required','date_format:H:i'],
            'checkout_time' => ['required','date_format:H:i'],
            'status' => ['required',Rule::in(['pending','approved','rejected','suspended'])],
        ];
    }
}
