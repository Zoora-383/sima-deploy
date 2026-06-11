<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending_pust,in_progress,done,rejected'],
            'note'   => ['nullable', 'string', 'max:500'],

            // SPK fields, required when status is in_progress (Final Approval by Kel Pust)
            'tanggal_mulai_efektif'   => ['required_if:status,in_progress', 'nullable', 'date'],
            'tanggal_selesai_target'  => ['required_if:status,in_progress', 'nullable', 'date', 'after_or_equal:tanggal_mulai_efektif'],
            'pagu_anggaran_disetujui' => ['required_if:status,in_progress', 'nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib diisi.',
            'status.in'       => 'Status tidak valid.',
        ];
    }
}