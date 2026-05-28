<?php

namespace App\Http\Requests\Item;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ItemStoreRequest extends FormRequest
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
     * @return array{category: string, description: string, image_item: string, location: string, name: string, type: string, unit: string}
     */
    public function rules(): array
    {
        return [
            'category' => 'required|string|exists:item_categories,name',
            'name'     => 'required|string',
            'type'     => 'required|string|in:logistic,non-logistic,service',
            'unit'     => 'nullable|integer',
            'image_item'  => 'nullable|string',
            'location'    => 'nullable|string',
            'description' => 'nullable|string',
        ];
    }
}
