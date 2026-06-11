<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SPKIndexResource extends JsonResource
{
    /**
     * Summary of toArray
     * @param Request $request
     * @return array{disetujui_oleh: mixed, nomor_spk: mixed, tanggal_mulai_efektif: mixed, tanggal_selesai_target: mixed, uuid: mixed}
     */
    public function toArray(Request $request): array
    {
        $latestApproval = $this->approvalLogs->first();

        return [
            'uuid'                   => $this->uuid,
            'nomor_spk'              => $this->nomor_spk,
            'tanggal_mulai_efektif'  => $this->tanggal_mulai_efektif,
            'tanggal_selesai_target' => $this->tanggal_selesai_target,

            'disetujui_oleh'         => $latestApproval && $latestApproval->user
                ? $latestApproval->user->username
                : null,
        ];
    }
}
