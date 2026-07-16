<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleStoreRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            $currentUser = auth('api')->user();
            $newRoles = $this->roleService->addRole($request->validated(), $currentUser);

            return $this->successResponse(
                ['role' => new RoleResource($newRoles)],
                'Created roles successfully',
                201
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Role Store Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to create roles.');
        }
    }

    public function index()
    {
        try {
            $role = $this->roleService->getAllRoles();

            return $this->successResponse(
                RoleResource::collection($role),
                'Get all roles successfully'
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Role Index Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get roles.');
        }
    }

    public function update(RoleStoreRequest $request, string $uuid)
    {
        try {
            $currentUser = auth('api')->user();
            $updatedRole = $this->roleService->updateRole($request->validated(), $uuid, $currentUser);

            return $this->successResponse(
                new RoleResource($updatedRole),
                'Role updated successfully'
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Role Update Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update role.');
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $currentUser = auth('api')->user();
            $this->roleService->deleteRole($uuid, $currentUser);

            return $this->successResponse(null, 'Role deleted successfully');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Role Destroy Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete role.');
        }
    }
}
