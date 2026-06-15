<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceStoreRequest extends FormRequest
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
            'item_id'                        => 'required|uuid|exists:items,uuid',
            'title'                          => 'required|string|max:255',
            'priority'                       => 'required|in:high,medium,low',
            'type'                           => 'required|in:korektif,preventif',
            'description'                    => 'nullable|string',
            'estimated_day'                  => 'nullable|integer|min:0',
            'target_completion_expectations' => 'nullable|date',
            'total_estimated_cost'           => 'nullable|numeric|min:0',

            // Daftar item/layanan yang dibutuhkan
            'items'                         => 'nullable|array',
            'items.*'                       => 'array',
            'items.*.nama_item'             => 'required|string|max:255',
            'items.*.qty'                   => 'nullable|integer|min:1',
            'items.*.satuan'                => 'nullable|string|max:50',
            'items.*.estimasi_biaya_satuan' => 'nullable|numeric|min:0',
            'items.*.file'                  => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required'                => 'Aset wajib dipilih.',
            'item_id.exists'                  => 'Aset tidak ditemukan.',
            'title.required'                  => 'Judul pengajuan wajib diisi.',
            'priority.in'                     => 'Prioritas harus salah satu dari: high, medium, low.',
            'type.in'                         => 'Tipe harus salah satu dari: korektif, preventif.',
            'items.*.nama_item.required_with' => 'Nama item wajib diisi.',
            'items.*.file.mimes'              => 'File gambar harus berformat jpg, jpeg, png, atau webp.',
            'items.*.file.max'                => 'Ukuran file maksimal 2MB.',
        ];
    }
}
