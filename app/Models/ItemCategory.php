<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereUuid($value)
 * @mixin \Eloquent
 */
#[Fillable(['uuid', 'name'])]
class ItemCategory extends Model
{
    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
