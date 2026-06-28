<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\MaintenanceRekapRequest;
use App\Http\Requests\Maintenance\MaintenanceStatusRequest;
use App\Http\Requests\Maintenance\MaintenanceStoreRequest;
use App\Http\Requests\Maintenance\MaintenanceUpdateRequest;
use App\Http\Resources\MaintenanceDetailResource;
use App\Http\Resources\MaintenanceResource;
use App\Services\MaintenanceService;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
     * @bodyParam request_items array List of items for maintenance.
     * @bodyParam request_items[].nama_item string required The name of the item. Example: Sparepart A
     * @bodyParam request_items[].qty integer Quantity. Example: 1
     * @bodyParam request_items[].satuan string Unit. Example: pcs
     * @bodyParam request_items[].estimasi_biaya_satuan number Unit cost estimate. Example: 50000
     * @bodyParam request_items[].file file Item image.
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
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Maintenance Store Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to create maintenance request.', 500);
        }
    }

    public function index()
    {
        try {
            $currentUser = auth('api')->user();
            $maintenances = $this->maintenanceService->getAllMaintenance($currentUser);

            return $this->successResponse(
                MaintenanceResource::collection($maintenances),
                'Get all maintenance records successfully'
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Maintenance Index Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch maintenance records.', 500);
        }
    }

    public function show(string $uuid)
    {
        try {
            $currentUser = auth('api')->user();
            $maintenance = $this->maintenanceService->getDetailMaintenance($uuid, $currentUser);

            return $this->successResponse(
                new MaintenanceDetailResource($maintenance),
                'Get detail maintenance successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Maintenance Show Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch maintenance detail.', 500);
        }
    }

    /**
     * Update maintenance request.
     * @requestMediaType multipart/form-data
     * @bodyParam request_items array List of items for maintenance.
     * @bodyParam request_items[].id integer existing item ID.
     * @bodyParam request_items[].nama_item string required The name of the item. Example: Sparepart A
     * @bodyParam request_items[].qty integer Quantity. Example: 1
     * @bodyParam request_items[].satuan string Unit. Example: pcs
     * @bodyParam request_items[].estimasi_biaya_satuan number Unit cost estimate. Example: 50000
     * @bodyParam request_items[].file file Item image.
     */
    public function update(MaintenanceUpdateRequest $request, string $uuid)
    {
        try {
            $currentUser = auth('api')->user();
            $maintenance = $this->maintenanceService->updateMaintenance(
                $request->validated(),
                $uuid,
                $currentUser
            );

            return $this->successResponse(
                new MaintenanceDetailResource($maintenance),
                'Updated maintenance successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
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
            $currentUser = auth('api')->user();
            $this->maintenanceService->deleteMaintenance($uuid, $currentUser);

            return $this->successResponse(null, 'Deleted maintenance successfully');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
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
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Maintenance Update Status Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update maintenance status.', 500);
        }
    }

    public function updateRekap(MaintenanceRekapRequest $request, string $uuid)
    {
        try {
            $currentUser = auth('api')->user();
            $maintenance = $this->maintenanceService->getDetailMaintenance($uuid, $currentUser);
            $spk = \App\Models\SPK::where('maintenance_id', $maintenance->id)->first();

            if (!$spk) {
                return $this->errorResponse('SPK tidak ditemukan untuk maintenance ini.', 404);
            }

            $rekap = $this->maintenanceService->addRekapsMaintenance(
                $request->validated(),
                $spk->uuid,
                $currentUser
            );

            return $this->successResponse(
                $rekap,
                'Updated maintenance rekap successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Maintenance Update Rekap Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update maintenance rekap.', 500);
        }
    }

    public function indexRekap()
    {
        try {
            $rekaps = $this->maintenanceService->getAllRekaps();
            return $this->successResponse($rekaps, 'Get all rekaps successfully');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Maintenance Index Rekap Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch rekaps.', 500);
        }
    }

    public function showRekap(string $rekapUuid)
    {
        try {
            $rekap = $this->maintenanceService->getRekapDetail($rekapUuid);
            return $this->successResponse($rekap, 'Get detail rekap successfully');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('Maintenance Show Rekap Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch rekap detail.', 500);
        }
    }

    public function destroyRekap(string $rekapUuid)
    {
        try {
            $this->maintenanceService->deleteRekap($rekapUuid);
            return $this->successResponse(null, 'Deleted rekap successfully');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('Maintenance Destroy Rekap Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete rekap.', 500);
        }
    }
}
