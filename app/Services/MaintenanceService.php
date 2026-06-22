<?php

namespace App\Services;

use App\Models\Item;
use App\Models\MaintenanceItem;
use App\Models\MaintenanceRekap;
use App\Models\MaintenanceRequest;
use App\Models\SPK;
use App\Models\User;
use App\Traits\RecordApprovalLog;
use App\Traits\SecureImageUpload;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MaintenanceService
{
    use RecordApprovalLog, SecureImageUpload;

    protected $spkService;

    public function __construct(SPKService $spkService)
    {
        $this->spkService = $spkService;
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Generate nomor pengajuan urut otomatis: MNT-YYYY-XXXX
     */
    private function generateNomorPengajuan(): string
    {
        $tahunSekarang = Carbon::now()->format('Y');
        $prefix        = "MNT-{$tahunSekarang}-";

        $lastRequest = MaintenanceRequest::where('nomor_pengajuan', 'like', $prefix . '%')
            ->orderBy('nomor_pengajuan', 'desc')
            ->lockForUpdate() // Hindari race condition saat concurrent request
            ->first();

        $nextNumber = $lastRequest
            ? (int) substr($lastRequest->nomor_pengajuan, -4) + 1
            : 1;

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Upload satu file ke S3, return URL-nya.
     * Pisahkan dari transaksi DB — tangani rollback secara manual.
     */
    private function uploadToS3($file): string
    {
        return $this->secureUpload($file, 'maintenance_items');
    }

    /**
     * Hapus file dari S3 berdasarkan URL publik.
     * Dipanggil saat rollback atau saat item dihapus.
     */
    private function deleteFromS3(?string $imageUrl): void
    {
        $this->deleteFileFromS3($imageUrl);
    }

    /**
     * Proses array items: upload file dulu (di luar transaksi),
     * kemudian kembalikan array siap insert ke DB.
     *
     * Jika DB rollback, panggil cleanupUploadedFiles() dengan $uploadedUrls.
     */
    private function prepareItemsWithUpload(array $items): array
    {
        $prepared    = [];
        $uploadedUrls = [];

        foreach ($items as $itemData) {
            $imagePath = null;

            if (!empty($itemData['file'])) {
                $imagePath      = $this->uploadToS3($itemData['file']);
                $uploadedUrls[] = $imagePath;
            }

            $prepared[] = [
                'nama_item'             => $itemData['nama_item'],
                'image_item'            => $imagePath,
                'qty'                   => $itemData['qty'] ?? null,
                'satuan'                => $itemData['satuan'] ?? null,
                'estimasi_biaya_satuan' => $itemData['estimasi_biaya_satuan'] ?? 0,
            ];
        }

        return ['prepared' => $prepared, 'uploadedUrls' => $uploadedUrls];
    }

    /**
     * Hapus semua URL yang sudah terupload ke S3 (dipanggil saat rollback).
     */
    private function cleanupUploadedFiles(array $urls): void
    {
        foreach ($urls as $url) {
            $this->deleteFromS3($url);
        }
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    /**
     * Summary of addMaintenance
     * @param array $data
     * @param User $currentUser
     * @throws Exception
     * @return MaintenanceRequest
     */
    public function addMaintenance(array $data, User $currentUser): MaintenanceRequest
    {
        $uploadedUrls = [];

        try {
            $itemsPayload = [];
            if (!empty($data['items']) && is_array($data['items'])) {
                $result       = $this->prepareItemsWithUpload($data['items']);
                $itemsPayload = $result['prepared'];
                $uploadedUrls = $result['uploadedUrls'];
            }

            DB::beginTransaction();

            $item            = Item::where('uuid', $data['item_id'])->firstOrFail();
            $nomorPengajuan  = $this->generateNomorPengajuan();

            $newMaintenance = MaintenanceRequest::create([
                'uuid'                           => Str::uuid()->toString(),
                'nomor_pengajuan'                => $nomorPengajuan,
                'item_id'                        => $item->id,
                'requester_id'                   => $currentUser->id,
                'title'                          => $data['title'],
                'priority'                       => $data['priority'],
                'type'                           => $data['type'],
                'description'                    => $data['description'],
                'estimated_day'                  => $data['estimated_day'] ?? 0,
                'target_completion_expectations' => $data['target_completion_expectations'],
                'total_estimated_cost'           => $data['total_estimated_cost'] ?? 0,
                'status'                         => 'draft',
            ]);

            if (!empty($itemsPayload)) {
                $newMaintenance->maintenanceItems()->createMany($itemsPayload);
            }

            $this->recordLog(
                $newMaintenance,
                'none',
                'draft',
                'Maintenance request created as draft via wizard form',
                $currentUser->id
            );

            DB::commit();

            return $newMaintenance->load(['maintenanceItems', 'approvalLogs.user']);
        } catch (Exception $e) {
            DB::rollBack();

            $this->cleanupUploadedFiles($uploadedUrls);

            throw new Exception("Gagal membuat maintenance request: " . $e->getMessage());
        }
    }

    // =========================================================================
    // READ
    // =========================================================================

    /**
     * Get all maintenance requests
     * @param User $currentUser
     * @throws Exception
     */
    public function getAllMaintenance(User $currentUser)
    {
        try {
            $query = MaintenanceRequest::with([
                'item.category',
                'requester.userProfile',
            ]);

            if ($currentUser->role->name === 'admin') {
                $query->where('requester_id', $currentUser->id);
            }

            return $query->latest()->get();
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data maintenance: " . $e->getMessage());
        }
    }

    /**
     * Get detail maintenance request
     * @param string $maintenanceUuid
     * @param User $currentUser
     * @throws NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws Exception
     * @return MaintenanceRequest
     */
    public function getDetailMaintenance(string $maintenanceUuid, User $currentUser): MaintenanceRequest
    {
        try {
            $maintenance = MaintenanceRequest::with([
                'item.category',
                'requester.userProfile',
                'maintenanceItems',
                'approvalLogs.user.userProfile',
            ])->where('uuid', $maintenanceUuid)->first();

            if (!$maintenance) {
                throw new NotFoundHttpException('Maintenance not found.');
            }

            if ($currentUser->role->name === 'admin' && $maintenance->requester_id !== $currentUser->id) {
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to access this maintenance request.');
            }

            return $maintenance;
        } catch (NotFoundHttpException | \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil detail maintenance: " . $e->getMessage());
        }
    }

    // =========================================================================
    // UPDATE — Header + Items
    // =========================================================================

    /**
     * Update maintenance request
     * @param array $data
     * @param string $maintenanceUuid
     * @param User $currentUser
     * @throws NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws \InvalidArgumentException
     * @throws Exception
     * @return MaintenanceRequest
     */
    public function updateMaintenance(array $data, string $maintenanceUuid, User $currentUser): MaintenanceRequest
    {
        $maintenance = MaintenanceRequest::with('maintenanceItems')
            ->where('uuid', $maintenanceUuid)
            ->first();

        if (!$maintenance) {
            throw new NotFoundHttpException('Maintenance not found.');
        }

        if ($currentUser->role->name === 'admin' && $maintenance->requester_id !== $currentUser->id) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to update this maintenance request.');
        }

        $allowedStatuses = ['draft', 'rejected'];
        if (!in_array($maintenance->status, $allowedStatuses)) {
            throw new \InvalidArgumentException(
                "Pengajuan tidak dapat diubah karena sedang dalam proses validasi atau pengerjaan "
                    . "(Status saat ini: " . str_replace('_', ' ', $maintenance->status) . ")."
            );
        }

        $uploadedUrls   = [];
        $urlsToDelete   = [];

        try {
            // Step 1 — Proses items (upload file baru di luar transaksi)
            $incomingItems   = $data['items'] ?? [];
            $incomingIds     = array_filter(array_column($incomingItems, 'id'));
            $existingItems   = $maintenance->maintenanceItems->keyBy('id');

            // Tentukan item mana yang akan dihapus
            $itemsToDelete = $existingItems->filter(
                fn($item) => !in_array($item->id, $incomingIds)
            );

            // Upload file untuk item baru / item yang ganti file
            $preparedItems = [];
            foreach ($incomingItems as $itemData) {
                $imagePath = null;

                if (!empty($itemData['file'])) {
                    $imagePath      = $this->uploadToS3($itemData['file']);
                    $uploadedUrls[] = $imagePath;

                    // Jika item existing ganti file → tandai file lama untuk dihapus
                    if (!empty($itemData['id']) && $existingItems->has($itemData['id'])) {
                        $oldUrl = $existingItems->get($itemData['id'])->image_item;
                        if ($oldUrl) {
                            $urlsToDelete[] = $oldUrl;
                        }
                    }
                } else {
                    // Tidak ada file baru → pertahankan image lama jika ada
                    $imagePath = $existingItems->get($itemData['id'] ?? null)?->image_item;
                }

                $preparedItems[] = array_merge($itemData, ['image_item' => $imagePath]);
            }

            // Step 2 — Transaksi DB
            DB::beginTransaction();

            // Update header
            $maintenance->update([
                'title'                          => $data['title']                          ?? $maintenance->title,
                'priority'                       => $data['priority']                       ?? $maintenance->priority,
                'type'                           => $data['type']                           ?? $maintenance->type,
                'description'                    => $data['description']                    ?? $maintenance->description,
                'estimated_day'                  => $data['estimated_day']                  ?? $maintenance->estimated_day,
                'target_completion_expectations' => $data['target_completion_expectations'] ?? $maintenance->target_completion_expectations,
                'total_estimated_cost'           => $data['total_estimated_cost']           ?? $maintenance->total_estimated_cost,
            ]);

            // Hapus items yang sudah tidak ada di payload
            foreach ($itemsToDelete as $item) {
                $urlsToDelete[] = $item->image_item; // tandai untuk dihapus setelah commit
                $item->delete();
            }

            // Upsert items
            foreach ($preparedItems as $itemData) {
                if (!empty($itemData['id']) && $existingItems->has($itemData['id'])) {
                    // Update existing item
                    $existingItems->get($itemData['id'])->update([
                        'nama_item'             => $itemData['nama_item'],
                        'image_item'            => $itemData['image_item'],
                        'qty'                   => $itemData['qty'] ?? null,
                        'satuan'                => $itemData['satuan'] ?? null,
                        'estimasi_biaya_satuan' => $itemData['estimasi_biaya_satuan'] ?? 0,
                    ]);
                } else {
                    // Insert item baru
                    $maintenance->maintenanceItems()->create([
                        'nama_item'             => $itemData['nama_item'],
                        'image_item'            => $itemData['image_item'],
                        'qty'                   => $itemData['qty'] ?? null,
                        'satuan'                => $itemData['satuan'] ?? null,
                        'estimasi_biaya_satuan' => $itemData['estimasi_biaya_satuan'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            // Step 3 — Bersihkan file S3 lama SETELAH commit berhasil
            $this->cleanupUploadedFiles($urlsToDelete);

            return $maintenance->fresh()->load('maintenanceItems');
        } catch (NotFoundHttpException | \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();

            // Bersihkan file baru yang terlanjur terupload
            $this->cleanupUploadedFiles($uploadedUrls);

            throw new Exception("Gagal memperbarui maintenance: " . $e->getMessage());
        }
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    /**
     * Hapus maintenance request beserta file S3 item-itemnya.
     * @param string $maintenanceUuid
     * @param User $currentUser
     * @throws NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws Exception
     * @return MaintenanceRequest
     */
    public function deleteMaintenance(string $maintenanceUuid, User $currentUser): MaintenanceRequest
    {
        $maintenance = MaintenanceRequest::with('maintenanceItems')
            ->where('uuid', $maintenanceUuid)
            ->first();

        if (!$maintenance) {
            throw new NotFoundHttpException('Maintenance not found.');
        }

        if ($currentUser->role->name === 'admin' && $maintenance->requester_id !== $currentUser->id) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to delete this maintenance request.');
        }

        try {
            // Kumpulkan semua URL gambar sebelum dihapus dari DB
            $imageUrls = $maintenance->maintenanceItems
                ->pluck('image_item')
                ->filter()
                ->values()
                ->toArray();

            DB::beginTransaction();
            $maintenance->delete(); // cascade delete items jika ada foreign key cascade
            DB::commit();

            // Hapus file S3 setelah DB sukses
            $this->cleanupUploadedFiles($imageUrls);

            return $maintenance;
        } catch (NotFoundHttpException | \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Gagal menghapus maintenance: " . $e->getMessage());
        }
    }

    // =========================================================================
    // UPDATE STATUS + SPK
    // =========================================================================

    /**
     * Update status maintenance sesuai role dan alur persetujuan.
     */
    public function updateStatus(string $maintenanceUuid, array $data, User $currentUser): MaintenanceRequest
    {
        $maintenance = MaintenanceRequest::where('uuid', $maintenanceUuid)->first();

        if (!$maintenance) {
            throw new NotFoundHttpException('Maintenance not found.');
        }

        if ($currentUser->role->name === 'admin' && $maintenance->requester_id !== $currentUser->id) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to update the status of this maintenance request.');
        }

        $statusFrom = $maintenance->status;
        $statusTo   = $data['status'];

        if ($statusFrom === $statusTo) {
            throw new \InvalidArgumentException("Status baru tidak boleh sama dengan status saat ini.");
        }

        $roleTransitions = [
            'admin' => [
                'draft'        => ['pending_kasi'],
                'rejected'     => ['pending_kasi'], // Allow resubmission after rejection/revision
                'in_progress'  => ['done'],
            ],
            'kasi'     => [
                'pending_kasi' => ['pending_pust', 'rejected']
            ],
            'kel_pust' => [
                'pending_pust' => ['in_progress',  'rejected']
            ],
        ];

        // Super Admin can bypass transitions if needed, or add them here. 
        // For now, follow the strict flow.
        $roleName = $currentUser->role->name;
        $allowed = $roleTransitions[$roleName][$statusFrom] ?? [];

        if (!in_array($statusTo, $allowed)) {
            throw new \InvalidArgumentException(
                "Role {$roleName} tidak diizinkan mengubah status dari " . str_replace('_', ' ', $statusFrom) . " ke " . str_replace('_', ' ', $statusTo)
            );
        }

        try {
            DB::beginTransaction();

            // --- AUTOMATION: SPK CREATION ---
            // Triggered when Kel_Pust approves to in_progress
            if ($statusTo === 'in_progress' && $statusFrom === 'pending_pust') {
                $this->spkService->addSPK([
                    'tanggal_mulai_efektif'   => $data['tanggal_mulai_efektif']  ?? now()->toDateString(),
                    'tanggal_selesai_target'  => $data['tanggal_selesai_target'] ?? now()->addDays(7)->toDateString(),
                    'pagu_anggaran_disetujui' => $data['pagu_anggaran_disetujui'] ?? 0,
                    'note'                    => $data['note'] ?? 'SPK otomatis dibuat oleh sistem saat persetujuan Kepala Pustakawan.'
                ], $currentUser, $maintenance->uuid);
            }

            $maintenance->update(['status' => $statusTo]);

            $logNote = $data['note']
                ?? "Status diubah dari " . str_replace('_', ' ', $statusFrom)
                . " menjadi " . str_replace('_', ' ', $statusTo);

            $this->recordLog($maintenance, $statusFrom, $statusTo, $logNote, $currentUser->id);

            // --- AUTOMATION: REKAP CREATION ---
            // Triggered when Admin finishes to done
            if ($statusTo === 'done') {
                $spk = SPK::where('maintenance_id', $maintenance->id)->first();
                if ($spk) {
                    $this->addRekapsMaintenance($data, $spk->uuid);
                }
            }

            DB::commit();

            return $maintenance->fresh(['approvalLogs.user']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Gagal mengubah status maintenance: " . $e->getMessage());
        }
    }

    // Method rekaps for maintenance
    public function getAllRekaps()
    {
        try {
            return MaintenanceRekap::with(['spk.maintenance.item'])->latest()->get();
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data rekap: " . $e->getMessage());
        }
    }

    public function getRekapDetail(string $rekapUuid)
    {
        $rekap = MaintenanceRekap::with(['spk.maintenance.item', 'attachments'])
            ->where('uuid', $rekapUuid)
            ->first();

        if (!$rekap) {
            throw new NotFoundHttpException('Data rekap tidak ditemukan.');
        }

        return $rekap;
    }

    public function deleteRekap(string $rekapUuid)
    {
        $rekap = MaintenanceRekap::where('uuid', $rekapUuid)->first();

        if (!$rekap) {
            throw new NotFoundHttpException('Data rekap tidak ditemukan.');
        }

        try {
            $rekap->delete();
            return $rekap;
        } catch (Exception $e) {
            throw new Exception("Gagal menghapus rekap: " . $e->getMessage());
        }
    }

    public function addRekapsMaintenance(array $data, string $spkUuid)
    {
        $spk = SPK::where('uuid', $spkUuid)->first();

        if (!$spk) {
            throw new NotFoundHttpException('Surat kerja yang selesai gagal ditemukan');
        }

        try {
            $rekaps = MaintenanceRekap::where('spk_id', $spk->id)->first();

            $payload = [
                'status'                      => $data['status'] ?? ($rekaps->status ?? 'success'),
                'ringkasan_tindakan'          => $data['ringkasan_tindakan'] ?? ($rekaps->ringkasan_tindakan ?? null),
                'realisasi_biaya'             => $data['realisasi_biaya'] ?? ($rekaps->realisasi_biaya ?? null),
                'jadwal_preventif_berikutnya' => $data['jadwal_preventif_berikutnya'] ?? ($rekaps->jadwal_preventif_berikutnya ?? null),
            ];

            if (!$rekaps) {
                $payload['uuid']                   = (string) Str::uuid();
                $payload['spk_id']                 = $spk->id;
                $payload['tanggal_selesai_aktual'] = now()->toDateString();
                $rekaps = MaintenanceRekap::create($payload);
            } else {
                $rekaps->update($payload);
            }

            return $rekaps;
        } catch (Exception $e) {
            throw new Exception("Gagal memproses rekaps maintenance: " . $e->getMessage());
        }
    }
}
