<?php

namespace App\Http\Requests\Item;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ItemCategoryStoreRequest extends FormRequest
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
     * @return array{name: string}
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string'
        ];
    }
}
