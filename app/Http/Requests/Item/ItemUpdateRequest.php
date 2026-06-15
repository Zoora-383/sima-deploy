<?php

namespace App\Http\Requests\Item;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ItemUpdateRequest extends FormRequest
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
     * @return array{category_uuid: ?string, name: ?string, type: "logistic"|"non-logistic"|"service"|null, units: ?int, image_item: ?string, location: ?string, description: ?string}
     */
    public function rules(): array
    {
        return [
            'category_uuid' => 'nullable|uuid|exists:item_categories,uuid',
            'name'        => 'nullable|string',
            'type'        => 'nullable|string|in:logistic,non-logistic,service',
            'units'       => 'nullable|integer',
            'image_item'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'location'    => 'nullable|string',
            'description' => 'nullable|string',
        ];
    }
}
