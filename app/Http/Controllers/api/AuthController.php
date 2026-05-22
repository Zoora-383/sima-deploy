<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
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
            $identifier = filter_var($identifierRequest, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $credentials = [
                $identifier => $identifierRequest,
                'password'  => $request->input('password')
            ];

            $guard = auth('api');
            $token = $this->authService->login($credentials, $guard);
            $user  = $guard->user();

            return response()->json([
                'status'  => 'success',
                'message' => 'Login successfully',
                'data'    => [
                    'user'        => new UserResource($user),
                    'accessToken' => $token
                ]
            ]);
        } catch (AuthenticationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 401);
        } catch (Exception $e) {
            Log::error('Login Fatal Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong on our server.'
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout(auth('api'));

            return response()->json([
                'status'  => 'success',
                'message' => 'Logout successfully'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not invalidate token'
            ], 401);
        } catch (Exception $e) {
            Log::error('Logout Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error during logout'
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $newToken = $this->authService->refresh(auth('api'));

            return response()->json([
                'status'  => 'success',
                'message' => 'Refresh token successfully',
                'data'    => [
                    'accessToken' => $newToken
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token is invalid or expired'
            ], 401);
        } catch (Exception $e) {
            Log::error('Refresh Token Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error during token refresh'
            ], 500);
        }
    }
}
