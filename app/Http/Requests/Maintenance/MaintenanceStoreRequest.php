<?php

namespace App\Http\Requests\Maintenance;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MaintenanceStoreRequest extends FormRequest
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
     * @return array{description: string, estimated_day: string, priority: string, status: string, target_completion_expectations: string, title: string, total_estimated_cost: string, type: string}
     */
    public function rules(): array
    {
        return [
            'item_id'       => 'required|uuid|exists:items,uuid',
            'title'         => 'required|string|max:255',
            'priority'      => 'required|in:high,medium,low',
            'type'          => 'required|in:korektif,preventif',
            'description'   => 'nullable|string',
            'estimated_day' => 'nullable|integer',
            'target_completion_expectations' => 'nullable|date',
            'total_estimated_cost'           => 'nullable|numeric',
            'status'        => 'required|in:pending_kasi,pending_pust,in_progress,done,rejected'
        ];
    }
}
