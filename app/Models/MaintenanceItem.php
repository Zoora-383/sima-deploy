<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

#[Fillable(['uuid', 'maintenance_id', 'nama_item', 'image_item','qty', 'satuan', 'estimasi_biaya_satuan'])]
class MaintenanceItem extends Model
{
    public $table = 'maintenance_request_items';

    protected static function booted()
    {
        static::creating(function ($item) {
            $item->uuid = (string) Str::uuid();
        });
    }

    public function maintenanceRequest() {
        return $this->belongsTo(MaintenanceRequest::class);
    }
}
