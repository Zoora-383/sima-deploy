<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Tambahkan import ini agar Rule::bisa digunakan

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Pastikan diubah jadi true jika Anda menghandle auth lewat middleware API
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        \Illuminate\Support\Facades\Log::info('Avatar upload check:', [
            'has_file' => $this->hasFile('avatar'),
            'file_instance' => $this->file('avatar') ? get_class($this->file('avatar')) : null,
            'error_code' => $this->file('avatar')?->getError(),
            'error_message' => $this->file('avatar')?->getErrorMessage(),
            'is_valid' => $this->file('avatar')?->isValid(),
        ]);

        // Mengambil ID user yang sedang login via API Guard
        $userId = auth('api')->id(); 

        return [
            'email' => [
                'sometimes',
                'nullable', // Mengizinkan null jika dikirim kosong
                'email',
                // Mengabaikan id user saat ini agar tidak terkena error 'email already taken'
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'username' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                // Mengabaikan id user saat ini agar tidak terkena error 'username already taken'
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'fullname' => 'sometimes|nullable|string|max:255',
            'phone'    => 'sometimes|nullable|string|max:20',
            'location' => 'sometimes|nullable|string',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Tambahkan validasi avatar di sini
        ];
    }
}