<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
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
        $roleName = $currentUser->role->name ?? '';
        if ($roleName !== 'super-admin') {
            throw new AccessDeniedHttpException('Only super-admin can create new accounts.');
        }

        try {
            DB::beginTransaction();

            $newAccount = User::create([
                'uuid'     => Str::uuid()->toString(),
                'role_id'     => $data['role'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
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

        $checkUniqueUsername = UserProfile::where('username', $username)->count();
        if ($checkUniqueUsername > 0) {
            $username = $username . '-' . ($checkUniqueUsername + 1);
        }

        return $username;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return UserProfile
     * @throws ConflictHttpException|Exception
     */
    public function addMyProfile(array $data, User $currentUser)
    {
        try {
            DB::beginTransaction();

            if ($currentUser->userProfile()->exists()) {
                throw new ConflictHttpException('Kamu sudah ada profile, gunakan yang sudah ada yahh');
            }

            $username = $this->generateUniqueUsername($currentUser->email);

            $profile = $currentUser->userProfile()->create([
                'uuid'     => Str::uuid()->toString(),
                'username' => $username,
                'fullname' => $data['fullname'],
                'phone'    => $data['phone'],
                'location' => $data['location'],
                'avatar_url' => $data['avatar_url']
            ]);

            DB::commit();

            return $profile;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
