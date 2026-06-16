<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array{
     *   status: "pending_kasi"|"pending_pust"|"in_progress"|"done"|"rejected",
     *   note: ?string,
     *   tanggal_mulai_efektif: ?string,
     *   tanggal_selesai_target: ?string,
     *   pagu_anggaran_disetujui: ?float
     * }
     */
    public function rules(): array
    {
        return [
            // Semua kemungkinan status tujuan dari role manapun
            'status' => ['required', 'string', 'in:pending_kasi,pending_pust,in_progress,done,rejected'],
            'note'   => ['required_if:status,rejected', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'                       => 'Status wajib diisi.',
            'status.in'                             => 'Status tidak valid.',
            'tanggal_mulai_efektif.required_if'     => 'Tanggal mulai efektif wajib diisi saat menyetujui pengerjaan.',
            'tanggal_selesai_target.required_if'    => 'Tanggal selesai target wajib diisi saat menyetujui pengerjaan.',
            'tanggal_selesai_target.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'pagu_anggaran_disetujui.required_if'   => 'Pagu anggaran wajib diisi saat menyetujui pengerjaan.',
            'pagu_anggaran_disetujui.min'           => 'Pagu anggaran tidak boleh negatif.',
        ];
    }
}