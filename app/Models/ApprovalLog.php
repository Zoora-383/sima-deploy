<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $approvable_type
 * @property int $approvable_id
 * @property int $user_id
 * @property string $status_from
 * @property string $status_to
 * @property string|null $note
 * @property array|null $actor_snapshot
 * @property array|null $data_snapshot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $approvable
 * @property-read \App\Models\User|null $user
 * @mixin \Eloquent
 */
#[Fillable(['uuid', 'approvable_id', 'approvable_type', 'user_id', 'status_from', 'status_to', 'note', 'actor_snapshot', 'data_snapshot'])]
class ApprovalLog extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });

        // Enforce append-only audit trail at the model level
        static::updating(function ($model) {
            throw new \Exception("Audit logs are append-only and cannot be updated.");
        });

        static::deleting(function ($model) {
            throw new \Exception("Audit logs are append-only and cannot be deleted.");
        });
    }

    protected function casts(): array
    {
        return [
            'actor_snapshot' => 'array',
            'data_snapshot' => 'array',
        ];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}