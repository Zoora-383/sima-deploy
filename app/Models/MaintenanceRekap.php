<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['spk_id', 'tanggal_selesai_aktual', 'status_hasil', 'ringkasan_tindakan', 'realisasi_biaya', 'jadwal_preventif_berikutnya'])]
class MaintenanceRekap extends Model
{
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
