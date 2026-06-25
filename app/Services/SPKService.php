<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\SPK;
use App\Models\User;
use App\Traits\RecordApprovalLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SPKService
{
    use RecordApprovalLog;

    public static function generateNomorSpk(): string
    {
        $prefix = 'SPK';
        $tahunCurrent = Carbon::now()->format('Y');

        $lastSpk = SPK::whereYear('created_at', $tahunCurrent)
            ->latest('id')
            ->lockForUpdate()
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

    /**
     * Summary of addSPK
     * @param array $data
     * @param User $currentUser
     * @param mixed $maintenanceUuid
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     * @throws Exception
     * @return SPK
     */
    public function addSPK(array $data, User $currentUser, ?string $maintenanceUuid = null)
    {
        $uuid = $maintenanceUuid ?? $data['maintenance_uuid'] ?? null;

        if (!$uuid) {
            throw new InvalidArgumentException('UUID Maintenance wajib disertakan untuk membuat SPK.');
        }

        $maintenance = MaintenanceRequest::where('uuid', $uuid)->first();

        if (!$maintenance) {
            throw new NotFoundHttpException('Maintenance tidak ditemukan untuk pembuatan SPK.');
        }

        // Prevent duplicate SPK for the same maintenance request
        $existingSpk = SPK::where('maintenance_id', $maintenance->id)->first();
        if ($existingSpk) {
            return $existingSpk; // Return existing if already created
        }

        $allowedStatuses = ['pending_pust', 'in_progress'];
        if (!in_array($maintenance->status, $allowedStatuses)) {
            throw new InvalidArgumentException("SPK hanya dapat dibuat untuk pengajuan maintenance yang telah disetujui Kepala PUSTIKOM (Status saat ini: " . str_replace('_', ' ', $maintenance->status) . ").");
        }

        try {
            DB::beginTransaction();

            $newSpk = SPK::create([
                'uuid'                    => Str::uuid()->toString(),
                'maintenance_id'          => $maintenance->id,
                'nomor_spk'               => $this->generateNomorSpk(),
                'tanggal_mulai_efektif'   => $data['tanggal_mulai_efektif'],
                'tanggal_selesai_target'  => $data['tanggal_selesai_target'],
                'pagu_anggaran_disetujui' => $data['pagu_anggaran_disetujui'],
            ]);

            $this->recordLog(
                $newSpk,
                'none',
                'approved',
                $data['note'] ?? 'SPK otomatis disetujui saat pembuatan dari data maintenance.',
                $currentUser->id
            );

            // Auto-transition associated maintenance request status to 'in_progress'
            if ($maintenance->status === 'pending_pust') {
                $maintenance->update(['status' => 'in_progress']);
                $this->recordLog(
                    $maintenance,
                    'pending_pust',
                    'in_progress',
                    $data['note'] ?? 'Status otomatis diubah ke in progress karena SPK telah diterbitkan.',
                    $currentUser->id
                );
            }

            DB::commit();

            return $newSpk->load('approvalLogs.user');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Gagal membuat SPK dari Maintenance: " . $e->getMessage());
        }
    }

    public function getAllSPK()
    {
        try {
            return SPK::with('approvalLogs.user')
                ->latest()
                ->get();
        } catch (Exception $e) {
            throw new Exception("Failed to get all SPK: " . $e->getMessage());
        }
    }

    public function getDetailSpk(string $spkUuid)
    {
        $spkDetail = SPK::with(['maintenance', 'approvalLogs.user'])
            ->where('uuid', $spkUuid)
            ->first();

        if (!$spkDetail) {
            throw new NotFoundHttpException('SPK tidak ditemukan.');
        }

        try {
            return $spkDetail;
        } catch (Exception $e) {
            throw new Exception("Failed to get detail SPK: " . $e->getMessage());
        }
    }

    public function deleteSpk(string $SpkUuid)
    {
        $spkDetail = SPK::where('uuid', $SpkUuid)->first();

        if (!$spkDetail) {
            throw new NotFoundHttpException('Spk not found.');
        }

        try {
            $spkDetail->delete();

            return $spkDetail;
        } catch (Exception $e) {
            throw new Exception("Failed to delete spk: " . $e->getMessage());
        }
    }

    public function updateSpk(array $data, string $spkUuid)
    {
        $spk = SPK::where('uuid', $spkUuid)->first();

        if (!$spk) {
            throw new NotFoundHttpException('SPK tidak ditemukan.');
        }

        try {
            DB::beginTransaction();

            $updateData = [];

            if (isset($data['tanggal_mulai_efektif']))   $updateData['tanggal_mulai_efektif']   = $data['tanggal_mulai_efektif'];
            if (isset($data['tanggal_selesai_target']))  $updateData['tanggal_selesai_target']  = $data['tanggal_selesai_target'];
            if (isset($data['pagu_anggaran_disetujui'])) $updateData['pagu_anggaran_disetujui'] = $data['pagu_anggaran_disetujui'];

            if (!empty($updateData)) {
                $spk->update($updateData);
            }

            DB::commit();
            return $spk->fresh(['maintenance', 'approvalLogs.user']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update SPK: " . $e->getMessage());
        }
    }

    /**
     * Generate PDF stream for the SPK.
     * 
     * @param string $spkUuid
     * @return string
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function generateSpkPdf(string $spkUuid)
    {
        $spk = SPK::with(['maintenance.item.category', 'maintenance.requester.userProfile', 'approvalLogs.user.userProfile'])
            ->where('uuid', $spkUuid)
            ->first();

        if (!$spk) {
            throw new NotFoundHttpException('SPK tidak ditemukan.');
        }

        // Get the name of the Kepala UPT TIK who approved the SPK
        $approverLog = $spk->approvalLogs()
            ->where('status_to', 'approved')
            ->orderBy('id', 'desc')
            ->first();
        $approverName = $approverLog?->user?->userProfile?->fullname ?? 'Kepala UPT TIK';

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.spk', [
                'spk' => $spk,
                'approverName' => $approverName,
            ])->setOption('isRemoteEnabled', true);

            return $pdf->output();
        } catch (Exception $e) {
            throw new Exception("Gagal menghasilkan dokumen PDF SPK: " . $e->getMessage());
        }
    }
}
