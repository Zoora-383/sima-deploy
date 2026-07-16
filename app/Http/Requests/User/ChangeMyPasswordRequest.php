<?php

namespace App\Http\Requests\User;

use Hash;
// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangeMyPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Summary of rules
     * @return array{current_password: array<(callable(mixed ,mixed ,mixed ):void)|string>, password: string}
     */
    public function rules(): array
    {
        $user = auth('api')->user();

        return [
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (!$user || !Hash::check($value, $user->password)) {
                        \Illuminate\Support\Facades\Log::warning('SECURITY: Gagal verifikasi kata sandi (Change Password)', [
                            'user_id' => $user?->id,
                            'email' => $user?->email,
                            'ip' => request()->ip(),
                        ]);
                        $fail('Password lama yang Anda masukkan salah.');
                    }
                }
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'different:current_password',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex'            => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            'password.different'        => 'Password baru harus berbeda dari password lama.',
            'password.min'              => 'Password minimal 8 karakter.',
        ];
    }
}
