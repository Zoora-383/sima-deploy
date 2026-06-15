<?php

namespace App\Http\Requests\Item;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * @return array{category_uuid: string, description: string|null, image_item: string|null, location: string|null, name: string, type: string, units: int|null}
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:logistic,non-logistic,service'],
            'category_uuid' => ['required', 'uuid', 'exists:item_categories,uuid'],
            'location'      => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image_item'  => ['nullable', 'image', 'max:2048'],
            'units' => [
                Rule::requiredIf(fn() => $this->input('type') === 'logistic'),
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
