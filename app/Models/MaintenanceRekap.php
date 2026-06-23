<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property int $spk_id
 * @property string|null $tanggal_selesai_aktual
 * @property string $status
 * @property string|null $ringkasan_tindakan
 * @property numeric|null $realisasi_biaya
 * @property string $jadwal_preventif_berikutnya
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereJadwalPreventifBerikutnya($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereRealisasiBiaya($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereRingkasanTindakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereSpkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereTanggalSelesaiAktual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereUuid($value)
 * @mixin \Eloquent
 */
#[Fillable(['uuid', 'spk_id', 'tanggal_selesai_aktual', 'status', 'ringkasan_tindakan', 'realisasi_biaya', 'jadwal_preventif_berikutnya'])]
class MaintenanceRekap extends Model
{
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
