<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['uuid', 'nomor_pengajuan', 'item_id', 'requester_id', 'title', 'priority', 'type', 'description', 'estimated_day', 'target_completion_expectations', 'total_estimated_cost', 'status'])]
class MaintenanceRequest extends Model
{
    public function item() {
        return $this->belongsTo(Item::class);
    }

    public function requester() {
        return $this->belongsTo(User::class);
    }

    public function approvalLogs()
    {
        return $this->morphMany(ApprovalLog::class, 'approvable');
    }
}
