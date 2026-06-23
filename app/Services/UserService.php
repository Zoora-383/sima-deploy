<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Traits\SecureImageUpload;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    use SecureImageUpload;

    // SUPER ADMIN METHOD USER MANAGEMENT

    private function generateUniqueUsername(string $email)
    {
        $baseUsername = Str::before($email, '@');
        $username = Str::slug($baseUsername);

        $checkUniqueUsername = User::where('username', $username)->count();
        if ($checkUniqueUsername > 0) {
            $username = $username . ($checkUniqueUsername + 1);
        }

        return $username;
    }

    /**
     * @param array $data
     * @return User
     * @throws AccessDeniedHttpException|Exception
     */
    public function addUser(array $data)
    {
        $role = Role::where('uuid', $data['role_uuid'])->first();
        try {
            DB::beginTransaction();

            $username = $this->generateUniqueUsername($data['email']);

            $newAccount = User::create([
                'uuid'      => Str::uuid()->toString(),
                'role_id'   => $role->id,
                'email'     => $data['email'],
                'username'  => $username,
                'password'  => Hash::make($data['password']),
                'is_active' => true,
            ]);

            DB::commit();

            return $newAccount;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Summary of getAllUser
     * @throws Exception
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllUser()
    {
        try {
            $user = User::with(['role', 'userProfile'])->orderBy('email', 'asc')->paginate(10);

            return $user;
        } catch (Exception $e) {
            throw new Exception("Failed to get all users: " . $e->getMessage());
        }
    }

    /**
     * @param string $userUuid
     * @return User
     * @throws NotFoundHttpException|Exception
     */
    public function getUserById(string $userUuid)
    {
        try {
            $user = User::with('role', 'userProfile')->where('uuid', $userUuid)->first();

            if (!$user) {
                throw new NotFoundHttpException('User not found.');
            }

            return $user;
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("Failed to get detail user: " . $e->getMessage());
        }
    }

    /**
     * @param string $userUuid
     * @param User $currentUser
     * @throws NotFoundHttpException|AccessDeniedHttpException|Exception
     * @return User|\stdClass
     */
    public function deleteUser(string $userUuid, User $currentUser)
    {
        $user = User::with('role', 'userProfile')->where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        // 1. Prevent self-deletion
        if ($currentUser->id === $user->id) {
            throw new AccessDeniedHttpException('Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // 2. Prevent deleting the last active super-admin
        if ($user->role->name === 'super-admin') {
            $activeSuperAdminCount = User::whereHas('role', function ($q) {
                $q->where('name', 'super-admin');
            })->where('is_active', true)->count();

            if ($activeSuperAdminCount <= 1 && $user->is_active) {
                throw new AccessDeniedHttpException('Sistem harus memiliki setidaknya satu Super Admin yang aktif.');
            }
        }

        try {
            $user->delete();

            return $user;
        } catch (Exception $e) {
            throw new Exception("Failed to delete user: " . $e->getMessage());
        }
    }

    /**
     * @param array $data
     * @param string $userUuid
     * @param User $currentUser
     * @throws NotFoundHttpException|AccessDeniedHttpException|Exception
     * @return User|null
     */
    public function updateUser(array $data, string $userUuid, User $currentUser)
    {
        $user = User::with('role', 'userProfile')->where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        try {
            DB::beginTransaction();
            $userData = [];

            if (isset($data['role_uuid'])) {
                $role = Role::where('uuid', $data['role_uuid'])->first();
                if ($role) {
                    // Prevent changing own role if it's the last super-admin
                    if ($user->id === $currentUser->id && $user->role->name === 'super-admin' && $role->name !== 'super-admin') {
                        $activeSuperAdminCount = User::whereHas('role', function ($q) {
                            $q->where('name', 'super-admin');
                        })->where('is_active', true)->count();

                        if ($activeSuperAdminCount <= 1) {
                            throw new AccessDeniedHttpException('Anda tidak dapat mengubah role Anda sendiri karena Anda adalah satu-satunya Super Admin aktif.');
                        }
                    }
                    $userData['role_id'] = $role->id;
                }
            }

            if (isset($data['email']))     $userData['email'] = $data['email'];
            if (isset($data['username'])) $userData['username'] = $data['username'];

            if (!empty($userData)) {
                $user->update($userData);
            }

            $profileData = [];

            if (isset($data['fullname'])) $profileData['fullname'] = $data['fullname'];
            if (isset($data['phone']))     $profileData['phone'] = $data['phone'];
            if (isset($data['location']))   $profileData['location'] = $data['location'];

            if (!empty($profileData)) {
                $user->userProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    array_merge($profileData, [
                        'uuid' => $user->userProfile?->uuid ?? (string) Str::uuid()
                    ])
                );
            }

            DB::commit();
            return $user->fresh(['role', 'userProfile']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update user: " . $e->getMessage());
        }
    }

    /**
     * Summary of updateUserStatus
     * @param string $userUuid
     * @param bool $status
     * @param User $currentUser
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @throws Exception
     * @return User|\stdClass
     */
    public function updateUserStatus(string $userUuid, bool $status, User $currentUser)
    {
        $user = User::with('role', 'userProfile')->where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        // 1. Prevent self-deactivation
        if ($currentUser->id === $user->id && $status === false) {
            throw new AccessDeniedHttpException('Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        // 2. Prevent deactivating the last active super-admin
        if ($user->role->name === 'super-admin' && $status === false) {
            $activeSuperAdminCount = User::whereHas('role', function ($q) {
                $q->where('name', 'super-admin');
            })->where('is_active', true)->count();

            if ($activeSuperAdminCount <= 1) {
                throw new AccessDeniedHttpException('Sistem harus memiliki setidaknya satu Super Admin yang aktif.');
            }
        }

        try {
            DB::beginTransaction();

            $user->update(['is_active' => $status]);

            DB::commit();

            return $user;
        } catch (AccessDeniedHttpException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update user status: " . $e->getMessage());
        }
    }

    // PERSONAL USER METHOD ACCOUNT MANAGEMENT

    /**
     * Summary of addMyProfile
     * @param array $data
     * @param mixed $file
     * @param User $currentUser
     * @return \App\Models\UserProfile
     */
    public function addMyProfile(array $data, $file = null, User $currentUser)
    {
        try {
            DB::beginTransaction();

            // 1. Update User Account Data (Email/Username)
            $userData = [];
            if (isset($data['email']))    $userData['email'] = $data['email'];
            if (isset($data['username'])) $userData['username'] = $data['username'];

            if (!empty($userData)) {
                $currentUser->update($userData);
            }

            // 2. Handle Avatar Upload
            $existingProfile = $currentUser->userProfile;
            $avatarPath = $existingProfile?->avatar_url;

            if ($file) {
                $this->deleteFileFromS3($avatarPath);
                $avatarPath = $this->secureUpload($file, 'avatars');
            }

            // 3. Update or Create Profile Detail
            $profile = $currentUser->userProfile()->updateOrCreate(
                ['user_id' => $currentUser->id],
                [
                    'uuid'       => $existingProfile?->uuid ?? Str::uuid()->toString(),
                    'fullname'   => $data['fullname']     ?? $existingProfile?->fullname,
                    'phone'      => $data['phone']        ?? $existingProfile?->phone,
                    'location'   => $data['location']     ?? $existingProfile?->location,
                    'avatar_url' => $avatarPath,
                ]
            );

            DB::commit();
            return $profile;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param string $userUuid
     * @return User
     * @throws NotFoundHttpException
     */
    public function getMyProfile(string $userUuid)
    {
        $user = User::with(['role', 'userProfile'])->where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('User profile not found.');
        }

        return $user;
    }

    /**
     * @param string $userUuid
     * @return void
     * @throws NotFoundHttpException|Exception
     */
    public function deleteMyAccount(string $userUuid)
    {
        $user = User::where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('Account not found.');
        }

        try {
            $user->delete();
        } catch (Exception $e) {
            throw new Exception("Failed to delete account: " . $e->getMessage());
        }
    }

    /**
     * Summary of changeMyPassword
     * @param array $data
     * @param User $currentUser
     * @throws Exception
     * @return User
     */
    public function changeMyPassword(array $data, User $currentUser)
    {
        try {
            DB::beginTransaction();

            $currentUser->update([
                'password' => Hash::make($data['password']) // Ambil dari key array & di-hash
            ]);

            DB::commit();

            return $currentUser;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to change password: " . $e->getMessage());
        }
    }
}
