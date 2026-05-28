<?php

namespace App\Http\Controllers\api;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\UserDetailResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(UserStoreRequest $request)
    {
        try {
            $newAccount = $this->userService->addUser($request->validated());

            return $this->successResponse(
                ['user' => new UserResource($newAccount)],
                'Created account successfully',
                201
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('User Store Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to create account.');
        }
    }

    public function index()
    {
        try {
            $users = $this->userService->getAllUser();

            return $this->successResponse(
                UserResource::collection($users)->response()->getData(true),
                'Get all users successfully'
            );
        } catch (Exception $e) {
            Log::error('User Index Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get all users.');
        }
    }

    public function show(string $uuid)
    {
        try {
            $user = $this->userService->getUserById($uuid);

            return $this->successResponse(
                ['user' => new UserDetailResource($user)],
                'Get detail user successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('User Show Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get user.');
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $this->userService->deleteUser($uuid);

            return $this->successResponse(null, 'User deleted successfully');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('User Destroy Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete.');
        }
    }

    public function update(UserUpdateRequest $request, string $uuid)
    {
        try {
            $updateUser = $this->userService->updateUser($request->validated(), $uuid);

            return $this->successResponse(
                ['user' => new UserResource($updateUser)],
                'User updated successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('User Update Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update.');
        }
    }

    public function updateStatus(UserUpdateRequest $request, string $uuid)
    {
        try {
            $user = $this->userService->getUserById($uuid);
            $newStatus = !$user->is_active; 
            
            $updatedUser = $this->userService->updateUserStatus($uuid, $newStatus);

            $message = $newStatus ? 'User activated successfully' : 'User blocked successfully';

            return $this->successResponse(
                ['user' => new UserResource($updatedUser)],
                $message
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('User Status Update Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update user status.');
        }
    }
}
