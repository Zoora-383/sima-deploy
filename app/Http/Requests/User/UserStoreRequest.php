<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth('api')->user();
        return $user && $user->role->name === 'super-admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_uuid'  => 'required|uuid|exists:roles,uuid',
            'email'      => 'required|email|unique:users,email',
            'password'   => [
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
