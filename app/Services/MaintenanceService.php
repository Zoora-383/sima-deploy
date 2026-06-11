<?php

namespace App\Services;

use App\Models\ApprovalLog;
use App\Models\Item;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Traits\RecordApprovalLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MaintenanceService
{
    use RecordApprovalLog;

    protected $spkService;

    public function __construct(SPKService $spkService)
    {
        $this->spkService = $spkService;
    }

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
                'status' => 'draft'
            ]);

            $this->recordLog($newMaintenance, 'none', 'draft', 'Maintenance request created as draft', $currentUser->id);

            DB::commit();

            return $newMaintenance;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to add maintenance request: " . $e->getMessage());
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

    /**
     * Summary of updateMaintenance
     * @param array $data
     * @param string $maintenanceUuid
     * @throws NotFoundHttpException
     * @throws \InvalidArgumentException
     * @throws Exception
     * @return MaintenanceRequest|\stdClass
     */
    public function updateMaintenance(array $data, string $maintenanceUuid)
    {
        $maintenance = MaintenanceRequest::where('uuid', $maintenanceUuid)->first();

        if (!$maintenance) {
            throw new NotFoundHttpException('Maintenance not found.');
        }

        $allowedStatusesForUpdate = ['draft', 'rejected'];

        if (!in_array($maintenance->status, $allowedStatusesForUpdate)) {
            throw new \InvalidArgumentException(
                "Pengajuan tidak dapat diubah karena sedang dalam proses validasi atau pengerjaan (Status saat ini: " . str_replace('_', ' ', $maintenance->status) . ")."
            );
        }

        try {
            $maintenance->update([
                'title'                  => $data['title'] ?? $maintenance->title,
                'priority'               => $data['priority'] ?? $maintenance->priority,
                'type'                   => $data['type'] ?? $maintenance->type,
                'description'            => $data['description'] ?? $maintenance->description,
                'estimated_day'          => $data['estimated_day'] ?? $maintenance->estimated_day,
                'target_completion_expectations' => $data['target_completion_expectations'] ?? $maintenance->target_completion_expectations,
                'total_estimated_cost'   => $data['total_estimated_cost'] ?? $maintenance->total_estimated_cost,
            ]);

            return $maintenance;
        } catch (Exception $e) {
            throw new Exception("Gagal memperbarui data maintenance: " . $e->getMessage());
        }
    }

    /**
     * Summary of updateStatus
     * @param string $maintenanceUuid
     * @param array $data
     * @param User $currentUser
     * @throws NotFoundHttpException
     * @throws \InvalidArgumentException
     * @throws Exception
     * @return MaintenanceRequest|null
     */
    public function updateStatus(string $maintenanceUuid, array $data, User $currentUser): MaintenanceRequest
    {
        $maintenance = MaintenanceRequest::where('uuid', $maintenanceUuid)->first();

        if (!$maintenance) {
            throw new NotFoundHttpException('Maintenance not found.');
        }

        $statusFrom = $maintenance->status;
        $statusTo   = $data['status'];

        if ($statusFrom === $statusTo) {
            throw new \InvalidArgumentException("Status baru tidak boleh sama dengan status saat ini.");
        }

        $roleTransitions = [
            'admin'    => [
                'draft'        => ['pending_kasi'],
                'in_progress'  => ['done'],
                'pending_kasi' => ['rejected'],
                'pending_pust' => ['rejected'],
            ],


            'kasi'     => ['pending_kasi' => ['pending_pust', 'rejected']],
            'kel_pust' => ['pending_pust' => ['in_progress',  'rejected']],
        ];

        $allowed = $roleTransitions[$currentUser->role][$statusFrom] ?? [];

        if (!in_array($statusTo, $allowed)) {
            throw new \InvalidArgumentException(
                "Anda tidak memiliki izin untuk melakukan transisi status ini."
            );
        }

        try {
            DB::beginTransaction();

            $maintenance->update(['status' => $statusTo]);
            $logNote = $data['note'] ?? "Status diubah dari " . str_replace('_', ' ', $statusFrom) . " menjadi " . str_replace('_', ' ', $statusTo);
            $this->recordLog($maintenance, $statusFrom, $statusTo, $logNote, $currentUser->id);

            // Jika status berubah menjadi in_progress (Approval Final oleh Kel Pust), buat SPK otomatis
            if ($statusTo === 'in_progress') {
                $spkData = [
                    'maintenance_id'          => $maintenance->id,
                    'tanggal_mulai_efektif'   => $data['tanggal_mulai_efektif'],
                    'tanggal_selesai_target'  => $data['tanggal_selesai_target'],
                    'pagu_anggaran_disetujui' => $data['pagu_anggaran_disetujui'],
                    'note'                    => "SPK diterbitkan otomatis setelah persetujuan Kepala Pustakawan.",
                ];

                $this->spkService->addSPK($spkData, $currentUser);
            }

            DB::commit();

            return $maintenance->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update maintenance status: " . $e->getMessage());
        }
    }
}
