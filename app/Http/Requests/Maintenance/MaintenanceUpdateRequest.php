<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Header
            'title'                          => 'required|string|max:255',
            'priority'                       => 'required|in:high,medium,low',
            'type'                           => 'required|in:korektif,preventif',
            'description'                    => 'nullable|string',
            'estimated_day'                  => 'nullable|integer|min:0',
            'target_completion_expectations' => 'nullable|date',
            'total_estimated_cost'           => 'nullable|numeric|min:0',

            // request_items — id ada berarti update, tidak ada berarti insert baru
            'request_items'                          => 'nullable|array',
            'request_items.*'                        => 'array',
            'request_items.*.id'                     => 'nullable|integer|exists:maintenance_request_items,id',
            'request_items.*.nama_item'              => 'required|string|max:255',
            'request_items.*.qty'                    => 'nullable|integer|min:1',
            'request_items.*.satuan'                 => 'nullable|string|max:50',
            'request_items.*.estimasi_biaya_satuan'  => 'nullable|numeric|min:0',
            'request_items.*.image_item'             => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'                  => 'Judul pengajuan wajib diisi.',
            'priority.in'                     => 'Prioritas harus salah satu dari: high, medium, low.',
            'type.in'                         => 'Tipe harus salah satu dari: korektif, preventif.',
            'request_items.*.id.exists'               => 'Item tidak ditemukan.',
            'request_items.*.nama_item.required'      => 'Nama item wajib diisi.',
            'request_items.*.image_item.mimes'        => 'File gambar harus berformat jpg, jpeg, png, atau webp.',
            'request_items.*.image_item.max'          => 'Ukuran file maksimal 2MB.',
        ];
    }
}