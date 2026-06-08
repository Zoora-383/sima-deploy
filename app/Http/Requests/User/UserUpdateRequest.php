<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        // Ambil string UUID dari parameter route URL
        $userUuid = $this->route('uuid');
        
        // Cari ID user aslinya di database untuk kebutuhan ignore()
        $userId = User::where('uuid', $userUuid)->value('id');

        return [
            'role'     => 'sometimes|string|exists:roles,name',
            'email'    => [
                'sometimes',
                'email',
                // Masukkan $userId (bukan uuid) agar ignore berfungsi normal
                Rule::unique('users', 'email')->ignore($userId) 
            ],
            'username' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId)
            ],
            'fullname' => 'sometimes|string|max:255',
            'phone'    => 'sometimes|string|max:20',
            'location' => 'sometimes|string',
        ];
    }
}