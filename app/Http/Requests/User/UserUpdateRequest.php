<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();
        return $user && $user->role->name === 'super-admin';
    }

    public function rules(): array
    {
        $userUuid = $this->route('uuid') ?? $this->segment(3);

        $user = User::where('uuid', $userUuid)->first();
        $userId = $user ? $user->id : null;

        return [
            'role_uuid' => 'sometimes|uuid|exists:roles,uuid',
            'email'    => [
                'sometimes',
                'email',
                $userId ? Rule::unique('users', 'email')->ignore($userId) : 'nullable'
            ],
            'username' => [
                'sometimes',
                'string',
                'max:255',
                $userId ? Rule::unique('users', 'username')->ignore($userId) : 'nullable'
            ],
            'fullname' => 'sometimes|string|max:255',
            'phone'    => 'sometimes|string|max:20',
            'location' => 'sometimes|string',
        ];
    }
}