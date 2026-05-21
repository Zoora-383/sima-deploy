<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function show(Request $request)
    {
        $result = $this->userService->getMyProfile(auth('api')->id());
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}
