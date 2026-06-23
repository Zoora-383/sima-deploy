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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $approvable
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereApprovableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereApprovableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereStatusFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereStatusTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereUuid($value)
 * @mixin \Eloquent
 */
#[Fillable(['uuid', 'approvable_id', 'approvable_type', 'user_id', 'status_from', 'status_to', 'note'])]
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