<?php

namespace App\Services;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\Guard\JWTAuth;

class AuthService
{
    /**
     * @param array $credentials
     * @param \Tymon\JWTAuth\JWTGuard $guard
     * @return string
     * @throws AuthenticationException
     */
    public function login(array $credentials, $guard)
    {
        if (!$token = $guard->attempt($credentials)) {
            $inputType = array_key_first($credentials);
            throw new AuthenticationException(ucfirst($inputType) . ' or password incorrect');
        }

        return $token;
    }

    /**
     * @param \Tymon\JWTAuth\JWTGuard $guard
     * @return void
     * @throws JWTException
     */
    public function logout($guard)
    {
        try {
            $guard->logout();
        } catch (JWTException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("Logout failed: " . $e->getMessage());
        }
    }

    /**
     * @param \Tymon\JWTAuth\JWTGuard $guard
     * @return string
     * @throws JWTException
     */
    public function refresh($guard)
    {
        return $guard->refresh();
    }
}
