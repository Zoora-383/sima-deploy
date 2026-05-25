<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleService
{
    public function addRole(array $data, User $currentUser)
    {
        $roleName = $currentUser->role->name ?? '';
        if ($roleName !== 'super-admin') {
            throw new AccessDeniedHttpException('Only super-admin can create new roles.');
        }

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
}
