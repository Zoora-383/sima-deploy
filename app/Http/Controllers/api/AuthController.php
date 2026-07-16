<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $identifierRequest = $request->input('identifier');
            $field = filter_var($identifierRequest, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $credentials = [
                $field     => $identifierRequest,
                'password' => $request->input('password')
            ];

            $guard = auth('api');
            $result = $this->authService->login($credentials, $guard);

            $data = [
                'accessToken'           => $result['accessToken'],
                'force_password_change' => $result['force_password_change'],
            ];

            return $this->successResponse(
                $data,
                'Login successfully',
                200
            );
        } catch (AuthenticationException $e) {
            return $this->errorResponse($e->getMessage(), 401);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Login Fatal Error: An unexpected error occurred.');
            return $this->errorResponse('Something went wrong on our server.');
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout(auth('api'));

            return $this->successResponse(null, 'Logout successfully');
        } catch (JWTException $e) {
            return $this->errorResponse('Could not invalidate token', 401);
        } catch (Exception $e) {
            Log::error('Logout Error: ' . $e->getMessage());
            return $this->errorResponse('Server error during logout');
        }
    }

    public function refresh()
    {
        try {
            $newToken = $this->authService->refresh(auth('api'));

            return $this->successResponse(
                ['accessToken' => $newToken],
                'Refresh token successfully'
            );
        } catch (JWTException $e) {
            return $this->errorResponse('Token is invalid or expired', 401);
        } catch (Exception $e) {
            Log::error('Refresh Token Error: ' . $e->getMessage());
            return $this->errorResponse('Server error during token refresh');
        }
    }

    public function resetPassword(string $uuid)
    {
        try {
            $resetPassword = $this->authService->resetPassword($uuid);

            return $this->successResponse(null, 'Reset password successfully');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            Log::error('User Status Update Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update user status.');
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $currentUser = auth('api')->user();
            $this->authService->changePassword($currentUser->uuid, $request->validated('password'));

            return $this->successResponse(null, 'Password changed successfully');
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            Log::error('Change Password Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to change password.');
        }
    }
}
