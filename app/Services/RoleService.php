<?php

namespace App\Services;

use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleService
{
    /**
     * @param array $data
     * @return $newRoles
     * @throws Exception
     */
    public function addRole(array $data)
    {
        try {
            DB::beginTransaction();

            $newRoles = Role::create([
                'uuid' => Str::uuid()->toString(),
                'name' => $data['name'],
            ]);

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
     * return $role
     * @throws Exception
     */
    public function deleteRole(string $roleUuid)
    {
        $role = Role::where('uuid', $roleUuid)->first();

        if (!$role) {
            throw new NotFoundHttpException('Role not found.');
        }

        try {
            $role->delete();

            return $role;
        } catch (Exception $e) {
            throw new Exception("Failed to delete role: " . $e->getMessage());
        }
    }

    /**
     * Update existing role
     * @param array $data
     * @param string $roleUuid
     * @return \App\Models\Role
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     */
    public function updateRole(array $data, string $roleUuid)
    {
        $role = Role::where('uuid', $roleUuid)->first();
        if (!$role) {
            throw new NotFoundHttpException('Role not found.');
        }
        try {
            DB::beginTransaction();

            $role->update([
                'name' => $data['name']
            ]);

            DB::commit();

            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update role: " . $e->getMessage());
        }
    }
}
