<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\ItemCategoryStoreRequest;
use App\Http\Resources\ItemCategoryResource;
use App\Services\ItemService;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

// use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    protected $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function store(ItemCategoryStoreRequest $request)
    {
        try {
            $newCategory = $this->itemService->createItemCategory($request->validated());

            return $this->successResponse(
                ['category' => new ItemCategoryResource($newCategory)], 
                'Item category created successfully', 
                201
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Item Category Store Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to create item category.');
        }
    }

    public function index()
    {
        try {
            $category = $this->itemService->getAllCategories();

            return $this->successResponse(
                ['categories' => ItemCategoryResource::collection($category)], 
                'Get all categories successfully'
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Item Category Index Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get item categories.');
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $this->itemService->deleteItemCategory($uuid);

            return $this->successResponse(null, 'Item category deleted successfully');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Item Category Destroy Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete item category.');
        }
    }

    public function update(ItemCategoryStoreRequest $request, string $uuid)
    {
        try {
            $category = $this->itemService->updateItemCategory($uuid, $request->validated());

            return $this->successResponse(
                ['category' => new ItemCategoryResource($category)], 
                'Item category updated successfully'
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Item Category Update Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update item category.');
        }
    }
}
