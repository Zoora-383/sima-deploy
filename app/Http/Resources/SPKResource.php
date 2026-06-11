<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SPKResource extends JsonResource
{
    /**
     * Summary of toArray
     * @param Request $request
     * @return array{created_at: mixed, disetujui_oleh: array{email: mixed, note: mixed, status: mixed, tanggal_setuju: mixed, username: mixed|null, maintenance: mixed|\Illuminate\Http\Resources\MissingValue, nomor_spk: mixed, pagu_anggaran_disetujui: float, tanggal_mulai_efektif: mixed, tanggal_selesai_target: mixed, uuid: mixed}}
     */
    public function toArray(Request $request): array
    {
        $latestApproval = $this->approvalLogs->first();

        return [
            'uuid' => $this->uuid,
            'nomor_spk' => $this->nomor_spk,
            'tanggal_mulai_efektif' => $this->tanggal_mulai_efektif,
            'tanggal_selesai_target' => $this->tanggal_selesai_target,
            'pagu_anggaran_disetujui' => (float) $this->pagu_anggaran_disetujui,
            'created_at' => $this->created_at,

            'maintenance' => $this->whenLoaded('maintenance', function () {
                return [
                    'id' => $this->maintenance->id,
                ];
            }),

            'disetujui_oleh' => $latestApproval && $latestApproval->user ? [
                'username' => $latestApproval->user->username,
                'email'    => $latestApproval->user->email,
                'status'   => $latestApproval->status, 
                'note'     => $latestApproval->note,
                'tanggal_setuju' => $latestApproval->created_at,
            ] : null,
        ];
    }
}
