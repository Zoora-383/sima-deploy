<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $maintenance_id
 * @property string $nama_item
 * @property string|null $image_item
 * @property int|null $qty
 * @property string|null $satuan
 * @property numeric|null $estimasi_biaya_satuan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaintenanceRequest $maintenanceRequest
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereEstimasiBiayaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereImageItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereMaintenanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereNamaItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereUuid($value)
 * @mixin \Eloquent
 */
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
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_id');
    }
}
