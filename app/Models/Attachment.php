<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property string $attachable_type
 * @property int $attachable_id
 * @property string $nama_file
 * @property string $path_url
 * @property string $ukuran_file
 * @property string $konteks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $attachable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereAttachableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereAttachableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereKonteks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereNamaFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment wherePathUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereUkuranFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereUuid($value)
 * @mixin \Eloquent
 */
#[Fillable(['attachable', 'nama_file', 'path_url', 'ukuran_file', 'konteks'])]
class Attachment extends Model
{
    public function attachable()
    {
        return $this->morphTo();
    }
}
