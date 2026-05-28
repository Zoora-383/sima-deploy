<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['uuid', 'name'])]
class ItemCategory extends Model
{
    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
