<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\User\ProfileStoreRequest;
use App\Http\Resources\UserDetailResource;
use App\Http\Resources\UserProfileResource;
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

    /**
     * @param ProfileStoreRequest $request
     * @requestMediaType multipart/form-data
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProfileStoreRequest $request)
    {
        try {
            $currentUser = auth('api')->user();
            $file = $request->file('avatar');
            $this->userService->addMyProfile($request->validated(), $file, $currentUser);
            $user = User::with(['role', 'userProfile'])->where('uuid', $currentUser->uuid)->first();

            return $this->successResponse(
                ['profile' => new UserProfileResource($user)],
                'Profile created successfully.',
                201
            );
        } catch (Exception $e) {
            Log::error('Profile Store Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to create profile.');
        }
    }

    public function show()
    {
        try {
            $userUuid = auth('api')->user()->uuid;
            $user = $this->userService->getMyProfile($userUuid);

            return $this->successResponse(
                ['profile' => new UserDetailResource($user)],
                'Get profile successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('Profile Show Error: ' . $e->getMessage());
            return $this->errorResponse('Server error');
        }
    }

    public function destroy()
    {
        try {
            $userUuid = auth('api')->user()->uuid;
            $this->userService->deleteMyAccount($userUuid);

            return $this->successResponse(null, 'Delete account successfully');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('Profile Destroy Error: ' . $e->getMessage());
            return $this->errorResponse('Server error');
        }
    }

    public function updateMyPassword(ChangePasswordRequest $request)
    {
        try {
            $currentUser = auth('api')->user();
            $updatePassword = $this->userService->changeMyPassword($request->validated(), $currentUser);

            return $this->successResponse(
                null,
                'Password changed successfully'
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('Change password Error: ' . $e->getMessage());
            return $this->errorResponse('Server error');
        }
    }
}
