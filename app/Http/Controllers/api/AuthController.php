<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|min:4',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'  => 'required|string|in:super-admin,admin,kasi,kep_pustikom',
            'phone' => 'string|regex:/^[0-9]{10,11}$/|unique:users,phone',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid field',
                'errors'  => $validator->errors()
            ], 422);
        }

        $result = $this->authService->addAccount($request->all());
        return response()->json($result, $result['status'] === 'success' ? 201 : 500);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid field',
                'errors'  => $validator->errors()
            ], 422);
        }

        $result = $this->authService->login($request->only('email', 'password'));
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function logout(Request $request) {
        $result = $this->authService->logout();
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function refresh(Request $request) {
        $result = $this->authService->refresh();
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}
