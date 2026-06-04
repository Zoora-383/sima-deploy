<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['request_id', 'nomor_spk', 'tanggal_mulai_efektif', 'tanggal_selesai_target', 'pagu_anggaran_disetujui'])]
class SPK extends Model
{

}
