<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_sets_force_password_change_to_true_and_blocks_user_until_changed(): void
    {
        // 1. Setup Roles
        $superAdminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'super-admin']);
        $userRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);

        // 2. Setup Super Admin
        $superAdmin = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $superAdminRole->id,
            'email' => 'super@test.com',
            'username' => 'superadmin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);
        $superAdminToken = JWTAuth::fromUser($superAdmin);
        UserSession::create([
            'user_id' => $superAdmin->id,
            'jti' => JWTAuth::setToken($superAdminToken)->getPayload()->get('jti'),
            'device_info' => 'PHPUnit',
            'last_activity' => now(),
        ]);

        // 3. Setup Target User
        $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $userRole->id,
            'email' => 'user@test.com',
            'username' => 'normaluser',
            'password' => bcrypt('password'),
            'is_active' => 1,
            'force_password_change' => false,
        ]);

        // 4. Super Admin resets the user's password
        $response = $this->withHeader('Authorization', 'Bearer ' . $superAdminToken)
            ->postJson("/api/v1/reset-password/{$user->uuid}");

        $response->assertStatus(200);

        // 5. User logs in with default password 'changeme'
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'user@test.com',
            'password' => 'changeme',
        ]);

        $loginResponse->assertStatus(200);
        $userToken = $loginResponse->json('data.accessToken');

        // 6. User attempts to access /api/v1/profile with the new token
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/v1/profile');

        $profileResponse->assertStatus(403);

        // 7. User changes password using the appropriate route
        $changePasswordResponse = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->postJson('/api/v1/auth/change-password', [
                'password' => 'Newpassword123',
            ]);

        $changePasswordResponse->assertStatus(200);

        // Clear auth cached user to simulate a fresh request/process in production
        auth('api')->forgetUser();

        // 8. User attempts to access /api/v1/profile again
        $profileResponseAfterChange = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/v1/profile');

        $profileResponseAfterChange->assertStatus(200);
    }
}
