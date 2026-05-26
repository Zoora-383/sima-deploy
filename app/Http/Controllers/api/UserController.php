<?php

namespace App\Http\Controllers\api;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

            return response()->json([
                'status'  => 'success',
                'message' => 'Created account successfully',
                'data'    => [
                    'user' => new UserResource($newAccount)
                ]
            ], 201);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 403);
        } catch (Exception $e) {
            Log::error('User Store Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create account.'
            ], 500);
        }
    }

    public function index()
    {
        try {
            $users = $this->userService->getAllUser();

            return response()->json([
                'status'  => 'success',
                'message' => 'Get all users successfully',
                'data'    => [
                    'user' => $users
                ]
            ], 200);
        } catch (Exception $e) {
            Log::error('User Index Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to get all users.'
            ], 500);
        }
    }

    public function show(string $uuid)
    {
        try {
            $user = $this->userService->getUserById($uuid);

            return response()->json([
                'status'  => 'success',
                'message' => 'Get detail user successfully',
                'data'    => [
                    'user' => $user
                ]
            ], 200);
        } catch (Exception $e) {
            Log::error('User Show Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to get user.'
            ], 500);
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $deleteUser = $this->userService->deleteUser($uuid);

            return response()->json([
                'status'  => 'success',
                'message' => 'User deleted successfully',
            ], 200);
        } catch (Exception $e) {
            Log::error('User Destroy Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete.'
            ], 500);
        }
    }

    public function update(UserUpdateRequest $request, string $uuid)
    {
        try {
            $updateUser = $this->userService->updateUser($request->validated(), $uuid);

            return response()->json([
                'status'  => 'success',
                'message' => 'User deleted successfully',
                'data' =>   [
                    'user' => new UserResource($updateUser)
                ]
            ], 200);
        } catch (Exception $e) {
            Log::error('User Destroy Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete.'
            ], 500);
        }
    }
}
