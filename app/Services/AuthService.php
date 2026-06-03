<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\Guard\JWTAuth;

class AuthService
{
    /**
     * Summary of login
     * @param array $credentials
     * @param mixed $guard
     * @throws AuthenticationException
     * @return array{accessToken: mixed, force_password_change: bool}
     */
    public function login(array $credentials, $guard): array
    {
        if (!$token = $guard->attempt($credentials)) {
            $inputType = array_key_first($credentials);
            throw new AuthenticationException(ucfirst($inputType) . ' or password incorrect');
        }

        $user = $guard->user();

        if (!$user->is_active) {
            $guard->logout();
            throw new AuthenticationException('Your account has been blocked. Please contact the administrator.');
        }

        return [
            'accessToken'           => $token,
            'force_password_change' => (bool) $user->force_password_change,
        ];
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
     * Summary of resetPassword
     * @param string $userUuid
     * @throws NotFoundHttpException
     * @throws Exception
     * @return User|null
     */
    public function resetPassword(string $userUuid)
    {
        $user = User::where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        try {
            DB::beginTransaction();

            $user->update([
                'password'              => Hash::make('changeme'),
                'force_password_change' => true,
            ]);

            DB::commit();
            return $user->fresh(['role', 'userProfile']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to reset password: " . $e->getMessage());
        }
    }

    /**
     * Summary of changePassword
     * @param string $userUuid
     * @param string $newPassword
     * @throws NotFoundHttpException
     * @throws Exception
     * @return User|null
     */
    public function changePassword(string $userUuid, string $newPassword)
    {
        $user = User::where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        try {
            DB::beginTransaction();

            $user->update([
                'password'              => Hash::make($newPassword),
                'force_password_change' => false,
            ]);

            DB::commit();
            return $user->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to change password: " . $e->getMessage());
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
