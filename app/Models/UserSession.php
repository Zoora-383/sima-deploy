<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $jti
 * @property string|null $device_info
 * @property string $last_activity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereDeviceInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereJti($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereUserId($value)
 * @mixin \Eloquent
 */
class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'jti',
        'device_info',
        'last_activity'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
