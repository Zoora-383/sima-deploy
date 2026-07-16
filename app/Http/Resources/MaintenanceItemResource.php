<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'                  => $this->uuid,
            'nama_item'             => $this->nama_item,
            'image_item'            => \App\Traits\SecureImageUpload::getPresignedUrl($this->image_item),
            'qty'                   => $this->qty,
            'satuan'                => $this->satuan,
            'estimasi_biaya_satuan' => (float) $this->estimasi_biaya_satuan,
        ];
    }
}
