<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Pastikan ini di-import!

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Set ke true karena urusan hak akses udah diurus sama Middleware
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
            'role' => 'sometimes|string|exists:roles,uuid',
            // is_active karena di database lu tinyInteger(0/1), pakai boolean atau in:0,1
            // 'is_active' => 'sometimes|in:0,1',
            'email'     => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('uuid'), 'uuid')
            ],
            'username'  => [
                'sometimes',
                'string',
                'max:255',
                // Wajib di-ignore juga biar gak bentrok sama username dia saat ini
                Rule::unique('users', 'username')->ignore($this->route('uuid'), 'uuid')
            ],
            'password'  => 'sometimes|string|min:6',
            'fullname' => 'sometimes|string|max:255',
            'phone'     => 'sometimes|string|max:20',
            'location'   => 'sometimes|string',
        ];
    }
}