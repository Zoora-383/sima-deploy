<?php

namespace App\Http\Controllers\api;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
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
            $token = $this->authService->login($credentials, $guard);

            return $this->successResponse(
                ['accessToken' => $token],
                'Login successfully'
            );
        } catch (AuthenticationException $e) {
            return $this->errorResponse($e->getMessage(), 401);
        } catch (Exception $e) {
            Log::error('Login Fatal Error: ' . $e->getMessage());
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
}
