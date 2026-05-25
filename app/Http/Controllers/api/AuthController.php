<?php

namespace App\Http\Controllers\api;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
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
            $password = $request->input('password');

            $user = User::where('email', $identifierRequest)
                ->orWhereHas('userProfile', function ($q) use ($identifierRequest) {
                    $q->where('username', $identifierRequest); // Sudah pakai titik koma (;)
                })->first();


            if (!$user) {
                return response()->json(['message' => 'Akun kamu tidak ditemukan'], 401);
            }

            if (!Hash::check($password, $user->password)) {
                return response()->json(['message' => 'Password yang dimasukan salah'], 401);
            }

            $credentials = [
                'email'     => $user->email,
                'password'  => $request->input('password')
            ];

            $guard = auth('api');
            $token = $this->authService->login($credentials, $guard);
            $user  = $guard->user();

            return response()->json([
                'status'  => 'success',
                'message' => 'Login successfully',
                'data'    => [
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
