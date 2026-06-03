<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'        => $this->uuid,
            'user'        => $this->user->userProfile->fullname ?? $this->user->username ?? 'Unknown',
            'status_from' => $this->status_from,
            'status_to'   => $this->status_to,
            'note'        => $this->note,
            'created_at'  => $this->created_at,
        ];
    }
}
