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
     * @return array{pagu_anggaran_disetujui: string, tanggal_mulai_efektif: string, tanggal_selesai_target: string}
     */
    public function rules(): array
    {
        return [
            'tanggal_mulai_efektif'   => 'sometimes|date',
            'tanggal_selesai_target'  => 'sometimes|date|after_or_equal:tanggal_mulai_efektif',
            'pagu_anggaran_disetujui' => 'sometimes|numeric|min:0',
        ];
    }
}
