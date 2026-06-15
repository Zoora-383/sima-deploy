<?php

namespace App\Services;

use App\Models\ApprovalLog;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use App\Traits\RecordApprovalLog;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemService
{
    use RecordApprovalLog;

    // METHODS ITEMS CATEGORY

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
        $path = $file->store($folder, 's3');
        return Storage::disk('s3')->url($path);
    }

    private function deleteOldImage(?string $imageUrl): void
    {
        if (!$imageUrl) return;

        $baseUrl = Storage::disk('s3')->url('');
        $path = str_replace($baseUrl, '', $imageUrl);

        Storage::disk('s3')->delete($path);
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
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getAllItem(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return Item::select('name', 'image_item', 'code_item', 'location', 'type', 'uuid')
                ->get();
        } catch (Exception $e) {
            throw new Exception("Failed to get items: " . $e->getMessage());
        }
    }

    /**
     * Get detailed information of an item
     * @param string $itemUuid
     * @return Item
     * @throws NotFoundHttpException|Exception
     */
    public function getDetailItem(string $itemUuid): Item
    {
        try {
            $item = Item::with([
                'user:id,username',
                'user.userProfile:user_id,fullname',
                'approvedBy:id,username',
                'approvedBy.userProfile:user_id,fullname',
                'category:id,name',
                'approvalLogs.user.userProfile'
            ])->where('uuid', $itemUuid)->first();

            if (!$item) {
                throw new NotFoundHttpException('Item not found.');
            }

            return $item;
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("Failed to get detail item: " . $e->getMessage());
        }
    }

    /**
     * Update an existing item
     * @param string $itemUuid
     * @param array $data
     * @param mixed $file
     * @return Item
     * @throws NotFoundHttpException|Exception
     */
    public function updateItem(string $itemUuid, array $data, $file = null): Item
    {
        $item = Item::where('uuid', $itemUuid)->first();

        if (!$item) {
            throw new NotFoundHttpException('Item not found.');
        }

        if (in_array($item->status, ['active', 'maintenance'])) {
            throw new Exception('Active or maintenance items cannot be edited.');
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
                || (isset($data['category']) && $category->id !== $item->category_id);

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
            ]);

            DB::commit();
            return $item->fresh(['user:id,username', 'category:id,name']);
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update item: " . $e->getMessage());
        }
    }

    /**
     * Delete an item
     * @param string $itemUuid
     * @return string
     * @throws NotFoundHttpException|Exception
     */
    public function deleteItem(string $itemUuid): string
    {
        $item = Item::where('uuid', $itemUuid)->first();

        if (!$item) {
            throw new NotFoundHttpException('Item not found.');
        }

        if ($item->status === 'maintenance') {
            throw new Exception('Item is in maintenance and cannot be deleted.');
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
     * @throws \InvalidArgumentException
     * @throws Exception
     * @return Item|null
     */
    public function updateStatus(string $itemUuid, array $data, User $currentUser): Item
    {
        $item = Item::where('uuid', $itemUuid)->first();

        if (!$item) {
            throw new NotFoundHttpException('Item not found.');
        }

        $statusFrom = $item->status;
        $statusTo   = $data['status'];

        $roleTransitions = [
            'admin'    => [
                'draft'    => ['pending_kasi'],
                'revision' => ['pending_kasi'],
            ],
            'kasi'     => [
                'pending_kasi' => ['pending_pust', 'revision'],
            ],
            'kel_pust' => [
                'pending_pust' => ['active', 'revision'],
            ],
        ];

        $allowed = $roleTransitions[$currentUser->role][$statusFrom] ?? [];

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
            return $item->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update item status: " . $e->getMessage());
        }
    }
}
