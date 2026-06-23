<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property string $nomor_pengajuan
 * @property int $item_id
 * @property int $requester_id
 * @property string $title
 * @property string $priority
 * @property string $type
 * @property string|null $description
 * @property int|null $estimated_day
 * @property string|null $target_completion_expectations
 * @property numeric|null $total_estimated_cost
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalLog> $approvalLogs
 * @property-read int|null $approval_logs_count
 * @property-read \App\Models\Item $item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceItem> $maintenanceItems
 * @property-read int|null $maintenance_items_count
 * @property-read \App\Models\User $requester
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereEstimatedDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereNomorPengajuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereRequesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereTargetCompletionExpectations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereTotalEstimatedCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereUuid($value)
 * @mixin \Eloquent
 */
#[Fillable(['uuid', 'nomor_pengajuan', 'item_id', 'requester_id', 'title', 'priority', 'type', 'description', 'estimated_day', 'target_completion_expectations', 'total_estimated_cost', 'status'])]
class MaintenanceRequest extends Model
{
    public function item() {
        return $this->belongsTo(Item::class);
    }

    public function requester() {
        return $this->belongsTo(User::class);
    }

    public function maintenanceItems() {
        return $this->hasMany(MaintenanceItem::class, 'maintenance_id');
    }

    public function approvalLogs()
    {
        return $this->morphMany(ApprovalLog::class, 'approvable');
    }
}
