<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MaintenanceUpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'         => 'required|string|max:255',
            'priority'      => 'required|in:high,medium,low',
            'type'          => 'required|in:korektif,preventif',
            'description'   => 'nullable|string',
            'estimated_day' => 'nullable|integer',
            'target_completion_expectations' => 'nullable|date',
            'total_estimated_cost'           => 'nullable|numeric',
        ];
    }
}
