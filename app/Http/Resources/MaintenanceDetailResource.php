<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'            => $this->uuid,
            'nomor_pengajuan' => $this->nomor_pengajuan,
            'item'            => new ItemResource($this->item),
            'requester'       => new UserResource($this->requester),
            'title'           => $this->title,
            'priority'        => $this->priority,
            'type'            => $this->type,
            'description'     => $this->description,
            'estimated_day'   => $this->estimated_day,
            'target_completion_expectations' => $this->target_completion_expectations,
            'total_estimated_cost'           => $this->total_estimated_cost,
            'status'          => $this->status,
            'request_items'   => MaintenanceItemResource::collection($this->whenLoaded('maintenanceItems')),
            'approval_logs'   => ApprovalLogResource::collection($this->whenLoaded('approvalLogs')),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
