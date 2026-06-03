<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
// use App\Traits\CloudinaryUpload;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    // use CloudinaryUpload;

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
        $role = Role::where('name', $data['role'])->first();
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
     * @return $user
     * @throws Exception
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
     * @throws NotFoundHttpException|Exception
     * @return User|\stdClass
     */
    public function deleteUser(string $userUuid)
    {
        $user = User::with('role', 'userProfile')->where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
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
     * @throws NotFoundHttpException|Exception
     * @return User|null
     */
    public function updateUser(array $data, string $userUuid)
    {
        $user = User::with('role', 'userProfile')->where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        try {
            DB::beginTransaction();
            $userData = [];

            if (isset($data['role_id']))   $userData['role_id'] = $data['role_id'];
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
                $user->userProfile()->update($profileData);
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
     * @throws NotFoundHttpException
     * @throws Exception
     * @return User|\stdClass
     */
    public function updateUserStatus(string $userUuid, bool $status)
    {
        $user = User::with('role', 'userProfile')->where('uuid', $userUuid)->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }
        try {
            DB::beginTransaction();

            $user->update(['is_active' => $status]);

            DB::commit();

            return $user;
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

            $existingProfile = $currentUser->userProfile;
            $avatarPath = $existingProfile->avatar_url ?? null;

            if ($file) {
                if ($avatarPath) {
                    $oldPath = parse_url($avatarPath, PHP_URL_PATH);
                    $oldPath = ltrim($oldPath, '/');

                    $bucket = config('filesystems.disks.s3.bucket');
                    if (str_starts_with($oldPath, $bucket . '/')) {
                        $oldPath = substr($oldPath, strlen($bucket) + 1);
                    }

                    $exists = Storage::disk('s3')->exists($oldPath);
                    Log::info('Deleting old avatar', [
                        'path' => $oldPath,
                        'exists' => $exists
                    ]);

                    Storage::disk('s3')->delete($oldPath);
                }

                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('s3')->putFileAs('avatars', $file, $filename);
                $avatarPath = Storage::disk('s3')->url($path);
            }

            $profile = $currentUser->userProfile()->updateOrCreate(
                ['user_id' => $currentUser->id],
                [
                    'uuid'       => $existingProfile->uuid ?? Str::uuid()->toString(),
                    'fullname'   => $data['fullname']  ?? $existingProfile->fullname  ?? null,
                    'phone'      => $data['phone']     ?? $existingProfile->phone     ?? null,
                    'location'   => $data['location']  ?? $existingProfile->location  ?? null,
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
}
