<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function addAccount(array $data)
    {
        try {
            DB::beginTransaction();

            $username = $this->generateUniqueUsername($data['email']);

            $newAccount = User::create([
                'uuid'     => Str::uuid()->toString(),
                'name'     => $data['name'],
                'email'    => $data['email'],
                'username' => $username,
                'password' => Hash::make($data['password']),
                'role'     => $data['role'],
                'phone'    => !empty($data['phone']) ? '+62' . $data['phone'] : null,
            ]);

            /**
             * @var Tymon\JWTAuth\JWTGuard $auth
             */
            $auth = auth('api');
            $token = $auth->login($newAccount);

            DB::commit();

            return [
                'status'  => 'success',
                'message' => 'Created account successfully',
                'data'    =>  [
                    'user' => [
                        'uuid'  => $newAccount->uuid,
                        'name'  => $newAccount->name,
                        'email' => $newAccount->email,
                        'username'   => $username,
                        'phone'      => $newAccount->phone,
                        'created_at' => $newAccount->created_at,
                        'updated_at' => $newAccount->updated_at
                    ],
                    'accessToken' => $token
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function generateUniqueUsername(string $email)
    {
        $baseUsername = Str::before($email, '@');
        $username = Str::slug($baseUsername);

        $checkUniqueUsername = User::where('username', $username)->count();
        if ($checkUniqueUsername > 0) {
            $username = $username . '-' . ($checkUniqueUsername + 1);
        }

        return $username;
    }

    public function login(array $credentials)
    {
        try {
            /**
             * @var Tymon\JWTAuth\JWTGuard $auth
             */
            $auth = auth('api');

            if (!$token = $auth->attempt($credentials)) {
                return [
                    'status'  => 'error',
                    'message' => 'Email or password incorrect'
                ];
            }

            $user = $auth->user();

            try {
                $oldToken = JWTAuth::fromUser($user);

                if ($oldToken) {
                    JWTAuth::setToken($oldToken)->invalidate();
                }
            } catch (JWTException $e) {
                return [
                    'status'  => 'error',
                    'message' => $e->getMessage()
                ];
            }

            return [
                'status'  => 'success',
                'message' => 'Login successfully',
                'data'    => [
                    'user' => [
                        'uuid'  => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'role'     => $user->role,
                    ],
                    'accessToken' => $token
                ]
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function logout()
    {
        try {
            /**
             * @var Tymon\JWTAuth\JWTGuard $auth
             */
            $auth = auth('api');
            $auth->logout();

            return [
                'status'  => 'success',
                'message' => 'Logout successfully'
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function refresh()
    {
        try {
            /**
             * @var Tymon\JWTAuth\JWTGuard $auth
             */
            $auth = auth('api');
            $auth->refresh();

            return [
                'status'  => 'success',
                'message' => 'Refresh token successfully'
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
