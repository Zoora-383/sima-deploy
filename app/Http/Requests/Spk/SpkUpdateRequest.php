<?php

namespace App\Http\Requests\Spk;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SpkUpdateRequest extends FormRequest
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
     * @return array{pagu_anggaran_disetujui: float|null, tanggal_mulai_efektif: string|null, tanggal_selesai_target: string|null}
     */
    public function rules(): array
    {
        return [
            'tanggal_mulai_efektif'   => 'sometimes|date',
            'tanggal_selesai_target'  => 'sometimes|date|after_or_equal:tanggal_mulai_efektif',
            'pagu_anggaran_disetujui' => 'sometimes|numeric|min:0|max:999999999999',
        ];
    }

    public function messages(): array
    {
        return [
            'pagu_anggaran_disetujui.numeric' => 'Pagu anggaran harus berupa format angka.',
            'pagu_anggaran_disetujui.max' => 'Pagu anggaran tidak boleh melebihi 999.999.999.999.',
        ];
    }
}
