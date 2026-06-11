<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User not found.'
                ], 401);
            }

            if (! $user->is_active) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Your account is inactive. Please contact administrator.'
                ], 403);
            }
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 401);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
