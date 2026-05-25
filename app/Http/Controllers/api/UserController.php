<?php

namespace App\Http\Controllers\api;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileStoreRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
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
            $currentUser = auth('api')->user();
            $newAccount = $this->userService->addAccount($request->validated(), $currentUser);

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

    public function storeProfile(ProfileStoreRequest $request)
    {
        try {
            $currentUser = auth('api')->user();
            $this->userService->addMyProfile($request->validated(), $currentUser);
            $user = User::with(['role', 'userProfile'])->find($currentUser->id);

            return response()->json([
                'status'  => 'success',
                'message' => 'Profile created successfully.',
                'data'    => new UserResource($user)
            ], 201);
        } catch (Exception $e) {
            Log::error('Profile Store Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create account.'
            ], 500);
        }
    }

    public function show()
    {
        try {
            $userId = auth('api')->id();
            $user = $this->userService->getProfile($userId);

            return response()->json([
                'status'  => 'success',
                'message' => 'Get profile successfully',
                'data'    => new UserResource($user)
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            Log::error('User Show Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error'
            ], 500);
        }
    }

    public function destroy()
    {
        try {
            $userId = auth('api')->id();
            $this->userService->deleteAccount($userId);

            return response()->json([
                'status'  => 'success',
                'message' => 'Delete account successfully'
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            Log::error('User Destroy Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error'
            ], 500);
        }
    }
}
