<?php

namespace App\Http\Requests\Spk;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SpkStoreRequest extends FormRequest
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
     * @return array{maintenance_id: string, note: string, pagu_anggaran_disetujui: string, tanggal_mulai_efektif: string, tanggal_selesai_target: string}
     */
    public function rules(): array
    {
        return [
            'maintenance_id'          => 'required|integer|exists:maintenances,id',
            'tanggal_mulai_efektif'   => 'required|date',
            'tanggal_selesai_target'  => 'required|date|date_format:Y-m-d|after_or_equal:tanggal_mulai_efektif',
            'pagu_anggaran_disetujui' => 'nullable|numeric|min:0',
            'note'                    => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'maintenance_id.required'         => 'Data perbaikan (maintenance) asal wajib dipilih.',
            'maintenance_id.exists'           => 'Data perbaikan tidak valid atau tidak ditemukan di sistem.',
            'tanggal_mulai_efektif.required'  => 'Tanggal mulai efektif wajib diisi.',
            'tanggal_selesai_target.required' => 'Tanggal selesai target wajib diisi.',
            'tanggal_selesai_target.after_or_equal' => 'Tanggal selesai target tidak boleh mendahului tanggal mulai efektif.',
            'pagu_anggaran_disetujui.numeric' => 'Pagu anggaran harus berupa format angka.',
        ];
    }
}
