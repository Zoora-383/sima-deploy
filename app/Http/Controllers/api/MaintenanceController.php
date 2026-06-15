<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\MaintenanceStatusRequest;
use App\Http\Requests\Maintenance\MaintenanceStoreRequest;
use App\Http\Requests\Maintenance\MaintenanceUpdateRequest;
use App\Http\Resources\MaintenanceDetailResource;
use App\Http\Resources\MaintenanceResource;
use App\Services\MaintenanceService;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MaintenanceController extends Controller
{
    protected MaintenanceService $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Store a new maintenance request.
     * @requestMediaType multipart/form-data
     * @bodyParam items array List of items for maintenance.
     * @bodyParam items[].nama_item string required The name of the item. Example: Sparepart A
     * @bodyParam items[].qty integer Quantity. Example: 1
     * @bodyParam items[].satuan string Unit. Example: pcs
     * @bodyParam items[].estimasi_biaya_satuan number Unit cost estimate. Example: 50000
     * @bodyParam items[].file file Item image.
     */
    public function store(MaintenanceStoreRequest $request)
    {
        try {
            $currentUser    = auth('api')->user();
            $newMaintenance = $this->maintenanceService->addMaintenance(
                $request->validated(),
                $currentUser
            );

            return $this->successResponse(
                new MaintenanceResource($newMaintenance),
                'Created maintenance successfully',
                201
            );
        } catch (Exception $e) {
            Log::error('Maintenance Store Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to create maintenance request.', 500);
        }
    }

    public function index()
    {
        try {
            $maintenances = $this->maintenanceService->getAllMaintenance();

            return $this->successResponse(
                MaintenanceResource::collection($maintenances),
                'Get all maintenance records successfully'
            );
        } catch (Exception $e) {
            Log::error('Maintenance Index Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch maintenance records.', 500);
        }
    }

    public function show(string $uuid)
    {
        try {
            $maintenance = $this->maintenanceService->getDetailMaintenance($uuid);

            return $this->successResponse(
                new MaintenanceDetailResource($maintenance),
                'Get detail maintenance successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('Maintenance Show Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch maintenance detail.', 500);
        }
    }

    /**
     * Update maintenance request.
     * @requestMediaType multipart/form-data
     * @bodyParam items array List of items for maintenance.
     * @bodyParam items[].id integer existing item ID.
     * @bodyParam items[].nama_item string required The name of the item. Example: Sparepart A
     * @bodyParam items[].qty integer Quantity. Example: 1
     * @bodyParam items[].satuan string Unit. Example: pcs
     * @bodyParam items[].estimasi_biaya_satuan number Unit cost estimate. Example: 50000
     * @bodyParam items[].file file Item image.
     */
    public function update(MaintenanceUpdateRequest $request, string $uuid)
    {
        try {
            $maintenance = $this->maintenanceService->updateMaintenance(
                $request->validated(),
                $uuid
            );

            return $this->successResponse(
                new MaintenanceDetailResource($maintenance),
                'Updated maintenance successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Maintenance Update Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update maintenance.', 500);
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $this->maintenanceService->deleteMaintenance($uuid);

            return $this->successResponse(null, 'Deleted maintenance successfully');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('Maintenance Destroy Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete maintenance.', 500);
        }
    }

    public function updateStatus(MaintenanceStatusRequest $request, string $uuid)
    {
        try {
            $currentUser = auth('api')->user();
            $maintenance = $this->maintenanceService->updateStatus(
                $uuid,
                $request->validated(),
                $currentUser
            );

            return $this->successResponse(
                new MaintenanceDetailResource($maintenance),
                'Updated maintenance status successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Maintenance Update Status Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update maintenance status.', 500);
        }
    }
}
