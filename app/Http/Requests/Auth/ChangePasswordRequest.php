<?php

namespace App\Http\Requests\Auth;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            'password.min'   => 'Password minimal 8 karakter.',
        ];
    }
}
