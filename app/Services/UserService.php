<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    /**
     * @param array $data
     * @param User $currentUser
     * @return User
     * @throws AccessDeniedHttpException|Exception
     */
    public function addAccount(array $data, User $currentUser)
    {
        if ($currentUser->role !== 'super-admin') {
            throw new AccessDeniedHttpException('Only super-admin can create new accounts.');
        }

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

            DB::commit();

            return $newAccount;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
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

    /**
     * @param int $userId
     * @return User
     * @throws NotFoundHttpException
     */
    public function getProfile(int $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            throw new NotFoundHttpException('User profile not found.');
        }

        return $user;
    }

    /**
     * @param int $userId
     * @return void
     * @throws NotFoundHttpException|Exception
     */
    public function deleteAccount(int $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            throw new NotFoundHttpException('Account not found.');
        }

        try {
            $user->delete();
        } catch (Exception $e) {
            throw new Exception("Failed to delete account: " . $e->getMessage());
        }
    }
}
