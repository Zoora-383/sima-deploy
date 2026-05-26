<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['uuid', 'fullname', 'phone', 'location', 'avatar_url'])]
class UserProfile extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
