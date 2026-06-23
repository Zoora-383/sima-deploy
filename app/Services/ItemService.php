<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use App\Traits\RecordApprovalLog;
use App\Traits\SecureImageUpload;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemService
{
    use RecordApprovalLog, SecureImageUpload;

    /**
     * Create a new item category
     * @param array $data
     * @return ItemCategory
     * @throws Exception
     */
    public function createItemCategory(array $data): ItemCategory
    {
        try {
            return ItemCategory::create([
                'uuid' => Str::uuid()->toString(),
                'name' => $data['name']
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to create item category: " . $e->getMessage());
        }
    }

    /**
     * Get all item categories
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getAllCategories(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return ItemCategory::orderBy('name', 'asc')->get();
        } catch (Exception $e) {
            throw new Exception("Failed to get item categories: " . $e->getMessage());
        }
    }

    /**
     * Update an existing item category
     * @param string $categoryUuid
     * @param array $data
     * @return ItemCategory
     * @throws NotFoundHttpException|Exception
     */
    public function updateItemCategory(string $categoryUuid, array $data): ItemCategory
    {
        $category = ItemCategory::where('uuid', $categoryUuid)->first();

        if (!$category) {
            throw new NotFoundHttpException('Item category not found.');
        }

        try {
            $category->update(['name' => $data['name']]);
            return $category;
        } catch (Exception $e) {
            throw new Exception("Failed to update item category: " . $e->getMessage());
        }
    }

    /**
     * Delete an item category
     * @param string $categoryUuid
     * @return bool
     * @throws NotFoundHttpException|Exception
     */
    public function deleteItemCategory(string $categoryUuid): bool
    {
        $category = ItemCategory::where('uuid', $categoryUuid)->first();

        if (!$category) {
            throw new NotFoundHttpException('Item category not found.');
        }

        if ($category->items()->exists()) {
            throw new Exception('Item category is still in use by items and cannot be deleted.');
        }

        try {
            $category->delete();
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to delete item category: " . $e->getMessage());
        }
    }

    // METHOD ITEM

    private function uploadImage($file, string $folder): string
    {
        return $this->secureUpload($file, $folder);
    }

    private function deleteOldImage(?string $imageUrl): void
    {
        $this->deleteFileFromS3($imageUrl);
    }

    /**
     * Generate a unique code for the item
     * @param string $type
     * @param ItemCategory $category
     * @return string
     */
    private function generateCodeItem(string $type, ItemCategory $category): string
    {
        $typePrefix = match ($type) {
            'logistic'     => 'LOG',
            'non-logistic' => 'NON',
            'service'      => 'SVC',
            default        => 'ITM',
        };

        $categoryPrefix = strtoupper(substr($category->name, 0, 3));
        $prefix = "{$typePrefix}-{$categoryPrefix}-";

        $lastItem = Item::where('code_item', 'like', "{$prefix}%")
            ->orderByDesc('code_item')
            ->lockForUpdate()
            ->first();

        $nextNumber = $lastItem
            ? (int) substr($lastItem->code_item, -3) + 1
            : 1;

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new item
     * @param array $data
     * @param mixed $file
     * @param User $currentUser
     * @return Item
     * @throws NotFoundHttpException|Exception
     */
    public function createItem(array $data, $file = null, User $currentUser): Item
    {
        if ($currentUser->role->name !== 'admin') {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Only admins are allowed to create items.');
        }

        $category = ItemCategory::where('uuid', $data['category_uuid'])->first();

        if (!$category) {
            throw new NotFoundHttpException('Item category not found.');
        }

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($file) {
                $imagePath = $this->uploadImage($file, 'items');
            }

            $item = Item::create([
                'uuid'        => Str::uuid()->toString(),
                'user_id'     => $currentUser->id,
                'category_id' => $category->id,
                'code_item'   => $this->generateCodeItem($data['type'], $category),
                'name'        => $data['name'],
                'type'        => $data['type'],
                'status'      => 'draft',
                'units' => $data['type'] === 'logistic' ? ($data['units'] ?? null) : null,
                'image_item'  => $imagePath,
                'location'    => $data['location']    ?? null,
                'description' => $data['description'] ?? null,
            ]);

            DB::commit();
            return $item;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to create item: " . $e->getMessage());
        }
    }

    /**
     * Get all items
     * @param User $currentUser
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws Exception
     */
    public function getAllItem(User $currentUser): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        try {
            $query = Item::select('name', 'image_item', 'code_item', 'location', 'type', 'uuid', 'units', 'status');

            if ($currentUser->role->name === 'admin') {
                $query->where('user_id', $currentUser->id);
            }

            return $query->latest()->paginate(10);
        } catch (Exception $e) {
            throw new Exception("Failed to get items: " . $e->getMessage());
        }
    }

    /**
     * Summary of getDetailItem
     * @param string $itemUuid
     * @param User $currentUser
     * @throws NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws Exception
     * @return Item|\stdClass
     */
    public function getDetailItem(string $itemUuid, User $currentUser): Item
    {
        try {
            $item = Item::with([
                'user:id,username',
                'user.userProfile:user_id,fullname',
                'category:id,name',
                'approvalLogs.user.userProfile'
            ])->where('uuid', $itemUuid)->first();

            if (!$item) {
                throw new NotFoundHttpException('Item not found.');
            }

            if ($currentUser->role->name === 'admin' && $item->user_id !== $currentUser->id) {
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to access this item.');
            }

            return $item;
        } catch (NotFoundHttpException | \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("Failed to get detail item: " . $e->getMessage());
        }
    }

    /**
     * Summary of updateItem
     * @param string $itemUuid
     * @param array $data
     * @param mixed $file
     * @param User $currentUser
     * @throws NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws Exception
     * @return Item|\Eloquent|null
     */
    public function updateItem(string $itemUuid, array $data, $file = null, User $currentUser): Item
    {
        $item = Item::where('uuid', $itemUuid)->first();

        if (!$item) {
            throw new NotFoundHttpException('Item not found.');
        }

        if ($currentUser->role->name !== 'admin') {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Only admins are allowed to update items.');
        }

        if ($item->user_id !== $currentUser->id) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to update this item.');
        }

        if (!in_array($item->status, ['draft', 'revision', 'pending_kasi', 'active'])) {
            throw new Exception('Items cannot be edited at this stage.');
        }

        try {
            DB::beginTransaction();

            $category = $item->category;
            if (isset($data['category_uuid'])) {
                $category = ItemCategory::where('uuid', $data['category_uuid'])->first();
                if (!$category) {
                    throw new NotFoundHttpException('Item category not found.');
                }
            }

            $imagePath = $item->image_item;
            if ($file) {
                $this->deleteOldImage($imagePath);
                $imagePath = $this->uploadImage($file, 'items');
            }

            $needsNewCode = (isset($data['type']) && $data['type'] !== $item->type)
                || (isset($data['category_uuid']) && $category->id !== $item->category_id);

            $oldStatus = $item->status;
            $newStatus = $item->status;
            if ($oldStatus === 'pending_kasi') {
                $newStatus = 'draft';
            }

            $item->update([
                'category_id' => $category->id,
                'code_item'   => $needsNewCode
                    ? $this->generateCodeItem($data['type'] ?? $item->type, $category)
                    : $item->code_item,
                'name'        => $data['name']        ?? $item->name,
                'type'        => $data['type']         ?? $item->type,
                'units' => $data['type'] === 'logistic' || $item->type === 'logistic' ? ($data['units'] ?? $item->units) : null,
                'image_item'  => $imagePath,
                'location'    => $data['location']     ?? $item->location,
                'description' => $data['description']  ?? $item->description,
                'status'      => $newStatus,
            ]);

            if ($oldStatus !== $newStatus) {
                $this->recordLog($item, $oldStatus, $newStatus, 'Item updated and pulled back to draft', $currentUser->id);
            }

            DB::commit();
            return $item->fresh(['user:id,username', 'category:id,name']);
        } catch (NotFoundHttpException | \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update item: " . $e->getMessage());
        }
    }

    /**
     * Summary of deleteItem
     * @param string $itemUuid
     * @param User $currentUser
     * @throws NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws Exception
     * @return string
     */
    public function deleteItem(string $itemUuid, User $currentUser): string
    {
        $item = Item::where('uuid', $itemUuid)->first();

        if (!$item) {
            throw new NotFoundHttpException('Item not found.');
        }

        if ($currentUser->role->name !== 'admin') {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Only admins are allowed to delete items.');
        }

        if ($item->user_id !== $currentUser->id) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to delete this item.');
        }

        if (!in_array($item->status, ['draft', 'revision', 'active'])) {
            throw new Exception('Only draft, revision, or active items can be deleted or disposed.');
        }

        try {
            if ($item->status === 'active') {
                $item->update(['status' => 'disposed']);
                return 'Item disposed successfully.';
            }

            $item->delete();
            return 'Item deleted successfully.';
        } catch (Exception $e) {
            throw new Exception("Failed to delete item: " . $e->getMessage());
        }
    }

    /**
     * Summary of updateStatus
     * @param string $itemUuid
     * @param array $data
     * @param User $currentUser
     * @throws NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws \InvalidArgumentException
     * @throws Exception
     * @return Item|\Eloquent|null
     */
    public function updateStatus(string $itemUuid, array $data, User $currentUser): Item
    {
        $item = Item::where('uuid', $itemUuid)->first();

        if (!$item) {
            throw new NotFoundHttpException('Item not found.');
        }

        if ($currentUser->role->name === 'admin' && $item->user_id !== $currentUser->id) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to update the status of this item.');
        }

        $statusFrom = $item->status;
        $statusTo   = $data['status'];

        // Map 'rejected' from request to 'revision' in database if necessary
        if ($statusTo === 'rejected') {
            $statusTo = 'revision';
        }

        // Map 'pending_kasi' to 'pending_pust' for role 'admin' when resubmitting an item revised by kel_pust
        if ($currentUser->role->name === 'admin' && $statusTo === 'pending_kasi') {
            if ($statusFrom === 'revision') {
                $lastRevisionLog = $item->approvalLogs()
                    ->where('status_to', 'revision')
                    ->orderByDesc('id')
                    ->first();

                $rejectedByKelPust = false;
                if ($lastRevisionLog && $lastRevisionLog->user && $lastRevisionLog->user->role) {
                    if ($lastRevisionLog->user->role->name === 'kel_pust') {
                        $rejectedByKelPust = true;
                    }
                }

                if ($rejectedByKelPust) {
                    $statusTo = 'pending_pust';
                }
            }
        }

        $roleTransitions = [
            'admin'    => [
                'draft'    => ['pending_kasi'],
                'revision' => ['pending_kasi'],
            ],
            'kasi'     => [
                'draft'        => ['pending_pust'],
                'pending_kasi' => ['pending_pust', 'revision'],
            ],
            'kel_pust' => [
                'pending_pust' => ['active', 'revision'],
            ],
        ];

        if ($currentUser->role->name === 'admin' && $statusFrom === 'revision') {
            $lastRevisionLog = $item->approvalLogs()
                ->where('status_to', 'revision')
                ->orderByDesc('id')
                ->first();

            $rejectedByKelPust = false;
            if ($lastRevisionLog && $lastRevisionLog->user && $lastRevisionLog->user->role) {
                if ($lastRevisionLog->user->role->name === 'kel_pust') {
                    $rejectedByKelPust = true;
                }
            }

            if ($rejectedByKelPust) {
                $roleTransitions['admin']['revision'] = ['pending_pust'];
            }
        }

        $allowed = $roleTransitions[$currentUser->role->name][$statusFrom] ?? [];

        if (!in_array($statusTo, $allowed)) {
            throw new \InvalidArgumentException(
                "Anda tidak memiliki izin untuk melakukan transisi status ini."
            );
        }

        try {
            DB::beginTransaction();

            $updateData = ['status' => $statusTo];

            if ($statusTo === 'active') {
                $updateData['approved_by'] = $currentUser->id;
            }

            $item->update($updateData);
            $this->recordLog($item, $statusFrom, $statusTo, $data['note'] ?? null, $currentUser->id);

            DB::commit();
            return $item->fresh(['approvalLogs.user', 'category', 'user.userProfile']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update item status: " . $e->getMessage());
        }
    }
}
