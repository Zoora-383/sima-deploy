<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleService
{
    /**
     * @param array $data
     * @param User $currentUser
     * @return Role
     * @throws Exception
     */
    public function addRole(array $data, User $currentUser)
    {
        try {
            DB::beginTransaction();

            $newRoles = Role::create([
                'uuid' => Str::uuid()->toString(),
                'name' => $data['name'],
            ]);

            \Illuminate\Support\Facades\Log::info("Role created: ID={$newRoles->id}, Name={$newRoles->name} by Super Admin ID={$currentUser->id} ({$currentUser->username})");

            DB::commit();

            return $newRoles;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>|\Illuminate\Support\Collection<int, \stdClass>
     */
    public function getAllRoles()
    {
        try {
            $roles = Role::orderBy('name', 'asc')->get();

            return $roles;
        } catch (Exception $e) {
            throw new Exception("Failed to get all roles: " . $e->getMessage());
        }
    }

    /**
     * @param string $roleUuid
     * @param User $currentUser
     * @return Role
     * @throws Exception
     */
    public function deleteRole(string $roleUuid, User $currentUser)
    {
        $role = Role::where('uuid', $roleUuid)->first();

        if (!$role) {
            throw new NotFoundHttpException('Role not found.');
        }

        // 1. Protect system roles from deletion
        $systemRoles = ['super-admin', 'admin', 'kasi', 'kel_pust'];
        if (in_array($role->name, $systemRoles)) {
            throw new AccessDeniedHttpException("Role sistem '{$role->name}' tidak dapat dihapus.");
        }

        // 2. Reject delete if role is still in use
        $userCount = $role->users()->count();
        if ($userCount > 0) {
            throw new AccessDeniedHttpException("Role ini tidak dapat dihapus karena masih digunakan oleh {$userCount} pengguna.");
        }

        try {
            DB::beginTransaction();

            $roleName = $role->name;
            $roleId = $role->id;
            $role->delete();

            \Illuminate\Support\Facades\Log::info("Role deleted: ID={$roleId}, Name={$roleName} by Super Admin ID={$currentUser->id} ({$currentUser->username})");

            DB::commit();

            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to delete role: " . $e->getMessage());
        }
    }

    /**
     * Update existing role
     * @param array $data
     * @param string $roleUuid
     * @param User $currentUser
     * @return \App\Models\Role
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws \Exception
     */
    public function updateRole(array $data, string $roleUuid, User $currentUser)
    {
        $role = Role::where('uuid', $roleUuid)->first();
        if (!$role) {
            throw new NotFoundHttpException('Role not found.');
        }

        // 1. Protect system roles from rename
        $systemRoles = ['super-admin', 'admin', 'kasi', 'kel_pust'];
        if (in_array($role->name, $systemRoles)) {
            throw new AccessDeniedHttpException("Role sistem '{$role->name}' tidak dapat diubah namanya.");
        }

        // 2. Prevent renaming a custom role to a system role name
        if (in_array($data['name'], $systemRoles)) {
            throw new AccessDeniedHttpException("Tidak dapat mengubah nama role menjadi nama role sistem.");
        }

        try {
            DB::beginTransaction();

            $oldName = $role->name;
            $role->update([
                'name' => $data['name']
            ]);

            \Illuminate\Support\Facades\Log::info("Role updated: ID={$role->id}, OldName={$oldName}, NewName={$role->name} by Super Admin ID={$currentUser->id} ({$currentUser->username})");

            DB::commit();

            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update role: " . $e->getMessage());
        }
    }
}
