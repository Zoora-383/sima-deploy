<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class DeleteAccountRequest extends FormRequest
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
        $user = auth('api')->user();

        return [
            'password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (!$user || !Hash::check($value, $user->password)) {
                        $fail('Password yang Anda masukkan salah. Penghapusan akun dibatalkan.');
                    }
                },
            ],
        ];
    }
}
