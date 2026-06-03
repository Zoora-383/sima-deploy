<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\MaintenanceStatusRequest;
use App\Http\Requests\Maintenance\MaintenanceStoreRequest;
use App\Http\Resources\MaintenanceDetailResource;
use App\Http\Resources\MaintenanceResource;
use App\Services\MaintenanceService;
use Exception;
use Illuminate\Support\Facades\Log;

class MaintenanceController extends Controller
{
    protected $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    public function store(MaintenanceStoreRequest $request)
    {
        try {
            $currentUser = auth('api')->user();
            $newMaintenance = $this->maintenanceService->addMaintenance($request->validated(), $currentUser);

            return $this->successResponse(new MaintenanceResource($newMaintenance), 'Created maintenance successfully', 201);
        } catch (Exception $e) {
            Log::error('Maintenance Store Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to create maintenance request.');
        }
    }

    public function index()
    {
        try {
            $maintenances = $this->maintenanceService->getAllMaintenance();
            return $this->successResponse(MaintenanceResource::collection($maintenances), 'Get all maintenance records successfully');
        } catch (Exception $e) {
            Log::error('Maintenance Index Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch maintenance records.');
        }
    }

    public function show(string $uuid)
    {
        try {
            $maintenance = $this->maintenanceService->getDetailMaintenance($uuid);
            return $this->successResponse(new MaintenanceDetailResource($maintenance), 'Get detail maintenance successfully');
        } catch (Exception $e) {
            Log::error('Maintenance Show Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch maintenance detail.');
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $this->maintenanceService->deleteMaintenance($uuid);
            return $this->successResponse(null, 'Delete maintenance successfully');
        } catch (Exception $e) {
            Log::error('Maintenance Destroy Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete maintenance.');
        }
    }

    public function updateStatus(MaintenanceStatusRequest $request, string $uuid)
    {
        try {
            $currentUser = auth('api')->user();
            $maintenance = $this->maintenanceService->updateStatus($uuid, $request->validated(), $currentUser);

            return $this->successResponse(new MaintenanceDetailResource($maintenance), 'Updated maintenance status successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Maintenance Update Status Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update maintenance status.');
        }
    }
}
