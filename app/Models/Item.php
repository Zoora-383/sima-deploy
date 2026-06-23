<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $category_id
 * @property string $code_item
 * @property string $name
 * @property string $type
 * @property string $status
 * @property int|null $units
 * @property string|null $image_item
 * @property string|null $location
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalLog> $approvalLogs
 * @property-read int|null $approval_logs_count
 * @property-read \App\Models\ItemCategory $category
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCodeItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereImageItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUuid($value)
 * @mixin \Eloquent
 */
#[Fillable(['uuid', 'user_id', 'category_id', 'code_item', 'name', 'type', 'units', 'image_item', 'location', 'description', 'status'])]
class Item extends Model
{
    public function category()
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvalLogs()
    {
        return $this->morphMany(ApprovalLog::class, 'approvable');
    }
}
