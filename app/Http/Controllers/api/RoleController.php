<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleStoreRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Exception;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function store(RoleStoreRequest $request)
    {
        try {
            $newRoles = $this->roleService->addRole($request->validated());

            return response()->json([
                'status'  => 'success',
                'message' => 'Created roles successfully',
                'data'    => [
                    'role' => new RoleResource($newRoles),
                ]
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 403);
        } catch (Exception $e) {
            Log::error('Role Store Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create roles.'
            ], 500);
        }
    }

    public function index()
    {
        try {
            $role = $this->roleService->getAllRoles();

            return response()->json([
                'status'  => 'success',
                'message' => 'Get all roles successfully',
                'data'    => RoleResource::collection($role)
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 403);
        } catch (Exception $e) {
            Log::error('Role Store Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to get roles.'
            ], 500);
        }
    }

    public function update(RoleStoreRequest $request, string $uuid)
    {
        try {
            $updatedRole = $this->roleService->updateRole($request->validated(), $uuid);

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => new RoleResource($updatedRole)
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 403);
        } catch (Exception $e) {
            Log::error('Role Store Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update role.'
            ], 500);
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $role = $this->roleService->deleteRole($uuid);

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully'
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 403);
        } catch (Exception $e) {
            Log::error('Role Store Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete role.'
            ], 500);
        }
    }
}
