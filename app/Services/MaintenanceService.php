<?php

namespace App\Services;

use App\Models\Item;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MaintenanceService
{
    /**
     * Method untuk generate nomor pengajuan urut otomatis: MNT-YYYY-XXXX
     */
    private function generateNomorPengajuan(): string
    {
        $tahunSekarang = Carbon::now()->format('Y');
        $prefix = "MNT-" . $tahunSekarang . "-";
        $lastRequest = MaintenanceRequest::where('nomor_pengajuan', 'like', $prefix . '%')
            ->orderBy('nomor_pengajuan', 'desc')
            ->first();

        if (!$lastRequest) {
            $nextNumber = 1;
        } else {
            $lastNumberString = substr($lastRequest->nomor_pengajuan, -4);
            $nextNumber = (int)$lastNumberString + 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function addMaintenance(array $data, User $currentUser)
    {
        try {
            DB::beginTransaction();

            $itemUuid = $data['item_id'];
            $item = Item::where('uuid', $itemUuid)->firstOrFail();
            $nomorPengajuan = $this->generateNomorPengajuan();

            $newMaintenance = MaintenanceRequest::create([
                'uuid' => Str::uuid()->toString(),
                'nomor_pengajuan' => $nomorPengajuan,
                'item_id' => $item->id,
                'requester_id' => $currentUser->id,
                'title' => $data['title'],
                'priority' => $data['priority'],
                'type' => $data['type'],
                'description' => $data['description'],
                'estimated_day' => $data['estimated_day'],
                'target_completion_expectations' => $data['target_completion_expectations'],
                'total_estimated_cost' => $data['total_estimated_cost'],
                'status' => $data['status']
            ]);

            $this->recordLog($newMaintenance, 'none', $data['status'], 'Maintenance request created', $currentUser->id);

            DB::commit();

            return $newMaintenance;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to add maintenance request: " . $e->getMessage());
        }
    }

    /**
     * Update status of maintenance request (Approval, Revision, etc.)
     * 
     * @param string $maintenanceUuid
     * @param array $data (status, note)
     * @param User $currentUser
     * @return MaintenanceRequest
     * @throws NotFoundHttpException|Exception
     */
    public function updateStatus(string $maintenanceUuid, array $data, User $currentUser): MaintenanceRequest
    {
        $maintenance = MaintenanceRequest::where('uuid', $maintenanceUuid)->first();

        if (!$maintenance) {
            throw new NotFoundHttpException('Maintenance not found.');
        }

        try {
            DB::beginTransaction();

            $statusFrom = $maintenance->status;
            $statusTo = $data['status'];
            $note = $data['note'] ?? null;

            $maintenance->update([
                'status' => $statusTo
            ]);

            $this->recordLog($maintenance, $statusFrom, $statusTo, $note, $currentUser->id);

            DB::commit();

            return $maintenance->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update maintenance status: " . $e->getMessage());
        }
    }

    public function getAllMaintenance()
    {
        try {
            return MaintenanceRequest::with(['item.category', 'requester.userProfile'])->latest()->get();
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data maintenance: " . $e->getMessage());
        }
    }

    public function getDetailMaintenance(string $maintenanceUuid)
    {
        try {
            $maintenance = MaintenanceRequest::with([
                'item.category', 
                'requester.userProfile',
                'approvalLogs.user.userProfile'
            ])->where('uuid', $maintenanceUuid)->first();

            if (!$maintenance) {
                throw new NotFoundHttpException('Maintenance not found.');
            }

            return $maintenance;
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data maintenance: " . $e->getMessage());
        }
    }

    public function deleteMaintenance(string $maintenanceUuid)
    {
        $maintenance = MaintenanceRequest::where('uuid', $maintenanceUuid)->first();

        if (!$maintenance) {
            throw new NotFoundHttpException('Maintenance not found.');
        }

        try {
            $maintenance->delete();
            return $maintenance;
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data maintenance: " . $e->getMessage());
        }
    }
}
