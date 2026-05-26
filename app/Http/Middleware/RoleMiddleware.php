<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth('api')->user();

        if(!$user || !in_array($user->role->name ?? '', $roles)) {
            throw new AccessDeniedHttpException('Anda tidak memiliki hak akses untuk tindakan ini.');
        }

        return $next($request);
    }
}
