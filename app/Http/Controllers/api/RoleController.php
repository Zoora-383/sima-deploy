<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleStoreRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Exception;
use Illuminate\Http\Request;
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
            $currentUser = auth('api')->user();
            $newRoles    = $this->roleService->addRole($request->validated(), $currentUser);

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
                'message' => 'Failed to create account.'
            ], 500);
        }
    }
}
