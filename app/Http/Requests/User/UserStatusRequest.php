<?php

namespace App\Http\Requests\User;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserStatusRequest extends FormRequest
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
     * @return array{is_active: string}
     */
    public function rules(): array
    {
        return [
            'is_active' => 'required|boolean'
        ];
    }
}
