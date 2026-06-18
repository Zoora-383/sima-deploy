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
            'status' => ['required', 'string', 'in:pending_kasi,pending_pust,in_progress,done,rejected'],
            'note'   => ['required_if:status,rejected', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib diisi.',
            'status.in'       => 'Status tidak valid.',
            'note.required_if' => 'Catatan wajib diisi jika pengajuan ditolak.',
        ];
    }
}