<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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
