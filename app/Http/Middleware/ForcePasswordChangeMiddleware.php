<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChangeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if ($user && $user->force_password_change) {
            if (!$request->is('api/v1/auth/change-password')) {
                return response()->json([
                    'message' => 'Anda harus mengganti password terlebih dahulu.',
                    'force_password_change' => true,
                ], 403);
            }
        }

        return $next($request);
    }
}
