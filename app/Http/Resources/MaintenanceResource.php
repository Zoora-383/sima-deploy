<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'category'   => $this->item->category->name ?? 'Tanpa kategori',
            'priority' => $this->priority,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
}
