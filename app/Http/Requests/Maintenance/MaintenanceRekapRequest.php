<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceRekapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'                      => ['nullable', 'string', 'in:success,partial,failed'],
            'ringkasan_tindakan'          => ['nullable', 'string'],
            'realisasi_biaya'             => ['nullable', 'numeric', 'min:0'],
            'jadwal_preventif_berikutnya' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'           => 'Status hasil tidak valid.',
            'realisasi_biaya.min' => 'Realisasi biaya tidak boleh negatif.',
        ];
    }
}
