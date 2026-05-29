<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\ItemStoreRequest;
use App\Http\Requests\Item\ItemUpdateRequest;
use App\Http\Resources\ItemDetailResource;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Services\ItemService;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

// use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function store(ItemStoreRequest $request)
    {
        try {
            $currentUser = auth('api')->user();
            $file = $request->file('image_item');
            $item = $this->itemService->createItem($request->validated(), $file, $currentUser);
            
            return $this->successResponse(
                ['item' => new ItemResource($item)],
                'Item created successfully',
                201
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Item Store Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to create item.');
        }
    }

    public function index()
    {
        try {
            $items = $this->itemService->getAllItem();

            return $this->successResponse(
                ['items' => ItemResource::collection($items)], 
                'Get all items successfully'
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Item Index Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get items.');
        }
    }

    public function show(string $uuid)
    {
        try {
            $item = $this->itemService->getDetailItem($uuid);

            return $this->successResponse(
                ['item' => new ItemDetailResource($item)], 
                'Get detail item successfully'
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Item Show Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get detail item.');
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $message = $this->itemService->deleteItem($uuid);

            return $this->successResponse(null, $message);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Item Destroy Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete item.');
        }
    }

    public function update(ItemUpdateRequest $request, string $uuid)
    {
        try {
            $file = $request->file('image_item');
            $item = $this->itemService->updateItem($uuid, $request->validated(), $file);

            return $this->successResponse(
                ['item' => new ItemResource($item)], 
                'Updated item successfully'
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Item Update Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update item.');
        }
    }
}
