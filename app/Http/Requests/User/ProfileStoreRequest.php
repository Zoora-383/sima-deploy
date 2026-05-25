<?php

namespace App\Http\Requests\User;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fullname' => 'nullable|string|min:3',
            'phone'    => 'nullable|string|regex:/^[0-9+\s-]+$/|min:9|max:15',
            'location' => 'nullable|string|max:255',
            'avatar_url' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan angka, spasi, atau simbol + di depan.',
            'phone.min'   => 'Nomor telepon minimal harus 9 karakter.',
            'phone.max'   => 'Nomor telepon maksimal harus 15 karakter.',
        ];
    }
}
