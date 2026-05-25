<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'full_name', 'phone', 'location', 'avatar_url'])]
class UserProfile extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
