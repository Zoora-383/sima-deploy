<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uuid', 'approvable_id', 'approvable_type', 'user_id', 'status_from', 'status_to', 'note'])]
class ApprovalLog extends Model
{
    /**
     * Get the parent approvable model (Item or MaintenanceRequest).
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
