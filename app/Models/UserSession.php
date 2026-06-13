<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
