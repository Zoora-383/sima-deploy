<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileStoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Exception;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProfileController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(ProfileStoreRequest $request)
    {
        try {
            $currentUser = auth('api')->user();
            $this->userService->addMyProfile($request->validated(), $currentUser);
            $user = User::with(['role', 'userProfile'])->where('uuid', $currentUser->uuid)->first();

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
            $userUuid = auth('api')->user()->uuid;
            $user = $this->userService->getMyProfile($userUuid);

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
            $userUuid = auth('api')->user()->uuid;
            $this->userService->deleteMyAccount($userUuid);

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
