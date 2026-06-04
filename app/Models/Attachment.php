<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['attachable', 'nama_file', 'path_url', 'ukuran_file', 'konteks'])]
class Attachment extends Model
{
    protected function attachable()
    {
        return $this->morphTo();
    }
}
