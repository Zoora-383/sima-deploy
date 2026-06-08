<?php

namespace App\Services;

use App\Models\ApprovalLog;
use App\Models\SPK;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class SPKService
{
    public static function generateNomorSpk(): string
    {
        $prefix = 'SPK';
        $tahunCurrent = Carbon::now()->format('Y');

        $lastSpk = SPK::whereYear('created_at', $tahunCurrent)
            ->latest('id')
            ->first();

        if (!$lastSpk) {
            $nextNumber = 1;
        } else {
            $lastNumber = (int) substr($lastSpk->nomor_spk, -4);
            $nextNumber = $lastNumber + 1;
        }
        $sequence = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$tahunCurrent}-{$sequence}";
    }

    public function addSPK(array $data, User $currentUser)
    {
        try {
            DB::beginTransaction();

            $newSpk = SPK::create([
                'request_id' => $currentUser,
                'nomor_spk'  => $this->generateNomorSpk(),
                'tanggal_mulai_efektif'   => $data['tanggal_mulai_efektif'],
                'tanggal_selesai_target'  => $data['tanggal_selesai_target'],
                'pagu_anggaran_disetujui' => $data['pagu_anggaran_disetujui'],
            ]);

            DB::commit();

            return $newSpk;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update role: " . $e->getMessage());
        }
    }

    public function getAllSPK()
    {
        try {
            return SPK::select('name', 'image_item', 'code_item', 'location', 'type', 'uuid')->get();
        } catch (Exception $e) {
            throw new Exception("Failed to update role: " . $e->getMessage());
        }
    }

    public function getDetailSpk(string $uuid)
    {
        try {

        } catch (Exception $e) {
            throw new Exception("Failed to update role: " . $e->getMessage());
        }
    }
}
