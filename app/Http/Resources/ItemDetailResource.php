<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'         => $this->uuid,
            'code'         => $this->code_item,
            'name'         => $this->name,
            'type'         => $this->type,
            'status'       => $this->status,
            'units'        => $this->units,
            'image'        => $this->image_item,
            'location'     => $this->location,
            'description'  => $this->description,
            'category'     => $this->category->name ?? null,
            'requested_by' => $this->user->userProfile->fullname ?? $this->user->username ?? 'Unknown',
            'approved_by'  => $this->approvedBy->userProfile->fullname ?? $this->approvedBy->username ?? null,
            'approval_logs' => ApprovalLogResource::collection($this->whenLoaded('approvalLogs')),
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
