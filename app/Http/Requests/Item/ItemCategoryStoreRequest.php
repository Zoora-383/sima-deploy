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

    public function rules(): array
    {
        $categoryUuid = $this->route('uuid');

        return [
            'name' => [
                'required',
                'string',
                $categoryUuid 
                    ? 'unique:item_categories,name,' . $categoryUuid . ',uuid'
                    : 'unique:item_categories,name'
            ]
        ];
    }
}
