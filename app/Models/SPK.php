<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property int $maintenance_id
 * @property string $nomor_spk
 * @property string|null $tanggal_mulai_efektif
 * @property string|null $tanggal_selesai_target
 * @property numeric|null $pagu_anggaran_disetujui
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalLog> $approvalLogs
 * @property-read int|null $approval_logs_count
 * @property-read \App\Models\MaintenanceRequest $maintenance
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereMaintenanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereNomorSpk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK wherePaguAnggaranDisetujui($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereTanggalMulaiEfektif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereTanggalSelesaiTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereUuid($value)
 * @mixin \Eloquent
 */
#[Fillable(['uuid', 'maintenance_id', 'nomor_spk', 'tanggal_mulai_efektif', 'tanggal_selesai_target', 'pagu_anggaran_disetujui'])]
class SPK extends Model
{
    protected $table = 'spks';

    public function maintenance()
    {
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_id');
    }

    public function approvalLogs()
    {
        return $this->morphMany(ApprovalLog::class, 'approvable');
    }
}
