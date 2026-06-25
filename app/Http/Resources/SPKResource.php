<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SPKResource extends JsonResource
{
    /**
     * Summary of toArray
     * @param Request $request
     * @return array{uuid: mixed, nomor_spk: mixed, tanggal_mulai_efektif: mixed, tanggal_selesai_target: mixed, pagu_anggaran_disetujui: float, created_at: mixed, maintenance: mixed|\Illuminate\Http\Resources\MissingValue, disetujui_oleh: array{username: mixed, email: mixed, status: mixed, note: mixed, tanggal_setuju: mixed}|null, approval_logs: mixed}
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
                'status'   => $latestApproval->status_to, 
                'note'     => $latestApproval->note,
                'tanggal_setuju' => $latestApproval->created_at,
            ] : null,

            'approval_logs' => ApprovalLogResource::collection($this->whenLoaded('approvalLogs')),
        ];
    }
}
