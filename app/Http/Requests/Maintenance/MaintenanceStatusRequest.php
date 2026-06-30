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
     *   status: "pending_kasi"|"pending_pust"|"in_progress"|"done"|"revision",
     *   note: ?string,
     *   tanggal_mulai_efektif: ?string,
     *   tanggal_selesai_target: ?string,
     *   pagu_anggaran_disetujui: ?float
     * }
     */
    public function rules(): array
    {
        return [
            'status'                  => ['required', 'string', 'in:pending_kasi,pending_pust,in_progress,done,revision'],
            'note'                    => ['required_if:status,revision', 'nullable', 'string', 'max:500'],
            'tanggal_mulai_efektif'   => ['required_if:status,in_progress', 'nullable', 'date'],
            'tanggal_selesai_target'  => ['required_if:status,in_progress', 'nullable', 'date', 'after_or_equal:tanggal_mulai_efektif'],
            'pagu_anggaran_disetujui' => ['required_if:status,in_progress', 'nullable', 'numeric', 'min:0', 'max:999999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'                       => 'Status wajib diisi.',
            'status.in'                             => 'Status tidak valid.',
            'note.required_if'                      => 'Catatan wajib diisi jika pengajuan direvisi.',
            'tanggal_mulai_efektif.required_if'     => 'Tanggal mulai efektif wajib diisi saat menyetujui pengajuan.',
            'tanggal_selesai_target.required_if'    => 'Tanggal selesai target wajib diisi saat menyetujui pengajuan.',
            'tanggal_selesai_target.after_or_equal' => 'Tanggal selesai target harus setelah atau sama dengan tanggal mulai efektif.',
            'pagu_anggaran_disetujui.required_if'   => 'Pagu anggaran yang disetujui wajib diisi saat menyetujui pengajuan.',
            'pagu_anggaran_disetujui.numeric'       => 'Pagu anggaran harus berupa angka.',
            'pagu_anggaran_disetujui.min'           => 'Pagu anggaran tidak boleh bernilai negatif.',
            'pagu_anggaran_disetujui.max'           => 'Pagu anggaran tidak boleh melebihi 999.999.999.999.',
        ];
    }
}