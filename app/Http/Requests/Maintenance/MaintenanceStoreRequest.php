<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array{item_id: string, title: string, priority: "high"|"medium"|"low", type: "korektif"|"preventif", description: ?string, estimated_day: ?int, target_completion_expectations: ?string, total_estimated_cost: ?float, items: ?array}
     */
    public function rules(): array
    {
        return [
            // Header
            'item_id'                        => [
                'required',
                'uuid',
                Rule::exists('items', 'uuid'),
            ],
            'title'                          => 'required|string|max:255',
            'priority'                       => 'required|in:high,medium,low',
            'type'                           => 'required|in:korektif,preventif',
            'description'                    => 'nullable|string',
            'estimated_day'                  => 'nullable|integer|min:0',
            'target_completion_expectations' => 'nullable|date',
            'total_estimated_cost'           => 'nullable|numeric|min:0',

            // Daftar item/layanan yang dibutuhkan
            'request_items'                         => 'nullable|array',
            'request_items.*'                       => 'array',
            'request_items.*.nama_item'             => 'required|string|max:255',
            'request_items.*.qty'                   => 'nullable|integer|min:1',
            'request_items.*.satuan'                => 'nullable|string|max:50',
            'request_items.*.estimasi_biaya_satuan' => 'nullable|numeric|min:0',
            'request_items.*.image_item'            => 'nullable|file|mimes:jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:2048',
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
            'request_items.*.nama_item.required'      => 'Nama item wajib diisi.',
            'request_items.*.image_item.mimes'        => 'File gambar harus berformat jpg, jpeg, png, atau webp.',
            'request_items.*.image_item.max'          => 'Ukuran file maksimal 2MB.',
        ];
    }
}
