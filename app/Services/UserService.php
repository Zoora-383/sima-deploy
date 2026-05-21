<?php

namespace App\Services;

use App\Models\User;
use Exception;

class UserService
{
    public function getMyProfile(int $userId)
    {
        try {
            $user = User::where('id', $userId)->first();

            if(!$user) {
                return [
                    'status'  => 'error',
                    'message' => 'Nof found account'
                ];
            }

            return [
                'status'  => 'success',
                'message' => 'Get profile successfully',
                'data' => [
                    'uuid'  => $user->uuid,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'username'   => $user->username,
                    'role'       => $user->role,
                    'phone'      => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ];
        } catch(Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
