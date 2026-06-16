<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class ItemStatusRequest extends FormRequest
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
     * @return array{status: "pending_kasi"|"pending_pust"|"active"|"rejected", note: ?string}
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:pending_kasi,pending_pust,active,rejected',
            'note'   => 'required_if:status,rejected|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'      => 'Status wajib dipilih.',
            'status.in'            => 'Status tidak valid.',
            'note.required_if'     => 'Catatan/Alasan wajib diisi jika menolak atau meminta revisi.',
        ];
    }
}
