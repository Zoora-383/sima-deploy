<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\MaintenanceRequest;
use App\Models\SPK;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $superAdminToken;
    protected $admin;
    protected $adminToken;
    protected $kasi;
    protected $kasiToken;
    protected $kelPust;
    protected $kelPustToken;

    protected $category;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Roles
        $superAdminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'super-admin']);
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $kasiRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'kasi']);
        $kelPustRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'kel_pust']);

        // 2. Setup Users
        $this->superAdmin = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $superAdminRole->id,
            'email' => 'superadmin@test.com',
            'username' => 'superadmin',
            'password' => bcrypt('Password123'),
            'is_active' => 1,
        ]);
        $this->superAdmin->userProfile()->create([
            'uuid' => Str::uuid()->toString(),
            'fullname' => 'Super Admin Test',
        ]);

        $this->admin = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('Password123'),
            'is_active' => 1,
        ]);
        $this->admin->userProfile()->create([
            'uuid' => Str::uuid()->toString(),
            'fullname' => 'Admin Test',
        ]);

        $this->kasi = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $kasiRole->id,
            'email' => 'kasi@test.com',
            'username' => 'kasi',
            'password' => bcrypt('Password123'),
            'is_active' => 1,
        ]);
        $this->kasi->userProfile()->create([
            'uuid' => Str::uuid()->toString(),
            'fullname' => 'Kasi Test',
        ]);

        $this->kelPust = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $kelPustRole->id,
            'email' => 'kelpust@test.com',
            'username' => 'kelpust',
            'password' => bcrypt('Password123'),
            'is_active' => 1,
        ]);
        $this->kelPust->userProfile()->create([
            'uuid' => Str::uuid()->toString(),
            'fullname' => 'Kel Pust Test',
        ]);

        // 3. Generate tokens & active sessions
        $this->superAdminToken = JWTAuth::fromUser($this->superAdmin);
        UserSession::create([
            'user_id' => $this->superAdmin->id,
            'jti' => JWTAuth::setToken($this->superAdminToken)->getPayload()->get('jti'),
            'device_info' => 'PHPUnit',
            'last_activity' => now(),
        ]);

        $this->adminToken = JWTAuth::fromUser($this->admin);
        UserSession::create([
            'user_id' => $this->admin->id,
            'jti' => JWTAuth::setToken($this->adminToken)->getPayload()->get('jti'),
            'device_info' => 'PHPUnit',
            'last_activity' => now(),
        ]);

        $this->kasiToken = JWTAuth::fromUser($this->kasi);
        UserSession::create([
            'user_id' => $this->kasi->id,
            'jti' => JWTAuth::setToken($this->kasiToken)->getPayload()->get('jti'),
            'device_info' => 'PHPUnit',
            'last_activity' => now(),
        ]);

        $this->kelPustToken = JWTAuth::fromUser($this->kelPust);
        UserSession::create([
            'user_id' => $this->kelPust->id,
            'jti' => JWTAuth::setToken($this->kelPustToken)->getPayload()->get('jti'),
            'device_info' => 'PHPUnit',
            'last_activity' => now(),
        ]);

        // 4. Seeding Basic Data
        $this->category = ItemCategory::create([
            'uuid' => Str::uuid()->toString(),
            'name' => 'Electronic Devices',
        ]);

        $this->item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'user_id' => $this->admin->id,
            'code_item' => 'ITM-ELE-001',
            'name' => 'Lenovo ThinkPad',
            'type' => 'logistic',
            'status' => 'active',
            'location' => 'Office',
            'units' => 10,
        ]);
    }

    /**
     * Helper to make authenticated requests cleanly and clear cache.
     */
    protected function requestAs(string $token, string $method, string $uri, array $data = [])
    {
        auth()->forgetGuards();
        $this->app->forgetInstance('tymon.jwt.auth');
        $this->app->forgetInstance('tymon.jwt');
        
        $request = $this->withHeader('Authorization', 'Bearer ' . $token);

        switch (strtoupper($method)) {
            case 'GET':
                return $request->getJson($uri);
            case 'POST':
                return $request->postJson($uri, $data);
            case 'PUT':
                return $request->putJson($uri, $data);
            case 'PATCH':
                return $request->patchJson($uri, $data);
            case 'DELETE':
                return $request->deleteJson($uri, $data);
            default:
                return $request->json($method, $uri, $data);
        }
    }

    /**
     * Assert request status and dump response body if assertion fails.
     */
    protected function assertStatusOk($response, int $expectedStatus)
    {
        if ($response->status() !== $expectedStatus) {
            $trace = debug_backtrace();
            foreach ($trace as $frame) {
                if (isset($frame['file']) && str_contains($frame['file'], 'ApiSmokeTest.php')) {
                    dump("Error at status: " . $response->status() . " (called from line " . $frame['line'] . ")");
                    break;
                }
            }
            dump($response->getContent());
        }
        $response->assertStatus($expectedStatus);
        return $response;
    }

    /**
     * Test Auth APIs
     */
    public function test_auth_and_profile_endpoints(): void
    {
        // Login API
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'admin@test.com',
            'password' => 'Password123',
        ]);
        $this->assertStatusOk($loginRes, 200);
        $loginRes->assertJsonStructure(['status', 'message', 'data' => ['accessToken', 'force_password_change']]);

        // Profile Detail API
        $profileRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/profile');
        $this->assertStatusOk($profileRes, 200);

        // Profile Update API
        $profileUpdateRes = $this->requestAs($this->adminToken, 'PUT', '/api/v1/profile', [
            'fullname' => 'Admin Test Updated',
            'phone' => '+628123456789',
            'location' => 'Building A',
        ]);
        $this->assertStatusOk($profileUpdateRes, 200);

        // Profile Reset Password (Self)
        $selfResetRes = $this->requestAs($this->adminToken, 'PUT', '/api/v1/profile/reset-password', [
            'current_password' => 'Password123',
            'password' => 'NewPassword123',
        ]);
        $this->assertStatusOk($selfResetRes, 200);

        // Update token after password reset (since password reset doesn't invalidate, but let's be safe and login again)
        $newLoginRes = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'admin@test.com',
            'password' => 'NewPassword123',
        ]);
        $this->assertStatusOk($newLoginRes, 200);
        $newAdminToken = $newLoginRes->json('data.accessToken');

        // Change Password API (Auth Group)
        $changePasswordRes = $this->requestAs($newAdminToken, 'POST', '/api/v1/auth/change-password', [
            'password' => 'FinalPassword123',
        ]);
        $this->assertStatusOk($changePasswordRes, 200);

        // Refresh Token API (with the newest password token)
        $newLoginRes2 = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'admin@test.com',
            'password' => 'FinalPassword123',
        ]);
        $this->assertStatusOk($newLoginRes2, 200);
        $latestToken = $newLoginRes2->json('data.accessToken');

        $refreshRes = $this->requestAs($latestToken, 'POST', '/api/v1/auth/refresh');
        $this->assertStatusOk($refreshRes, 200);
        $refreshedToken = $refreshRes->json('data.accessToken');

        // Logout API
        $logoutRes = $this->requestAs($refreshedToken, 'POST', '/api/v1/auth/logout');
        $this->assertStatusOk($logoutRes, 200);
    }

    /**
     * Test User Management APIs (Super Admin)
     */
    public function test_super_admin_user_management_endpoints(): void
    {
        $roleAdmin = Role::where('name', 'admin')->first();

        // 1. Create User
        $userPayload = [
            'role_uuid' => $roleAdmin->uuid,
            'email' => 'newuser@test.com',
            'password' => 'Password123',
        ];
        $storeRes = $this->requestAs($this->superAdminToken, 'POST', '/api/v1/admin/users', $userPayload);
        $this->assertStatusOk($storeRes, 201);
        $userUuid = $storeRes->json('data.user.uuid');

        // 2. Index Users
        $indexRes = $this->requestAs($this->superAdminToken, 'GET', '/api/v1/admin/users');
        $this->assertStatusOk($indexRes, 200);

        // 3. Show User Detail
        $showRes = $this->requestAs($this->superAdminToken, 'GET', '/api/v1/admin/users/' . $userUuid);
        $this->assertStatusOk($showRes, 200);

        // 4. Update User
        $updateRes = $this->requestAs($this->superAdminToken, 'PUT', '/api/v1/admin/users/' . $userUuid, [
            'role_uuid' => $roleAdmin->uuid,
            'email' => 'updateduser@test.com',
        ]);
        $this->assertStatusOk($updateRes, 200);

        // 5. Change User Status (Disable)
        $statusRes = $this->requestAs($this->superAdminToken, 'PATCH', '/api/v1/admin/users/' . $userUuid . '/status', [
            'status' => false,
        ]);
        $this->assertStatusOk($statusRes, 200);

        // 6. Reset User Password (by Admin)
        $adminResetRes = $this->requestAs($this->superAdminToken, 'POST', '/api/v1/reset-password/' . $userUuid);
        $this->assertStatusOk($adminResetRes, 200);

        // 7. Delete User
        $deleteRes = $this->requestAs($this->superAdminToken, 'DELETE', '/api/v1/admin/users/' . $userUuid);
        $this->assertStatusOk($deleteRes, 200);
    }

    /**
     * Test Role Management APIs
     */
    public function test_role_management_endpoints(): void
    {
        // 1. Store Role
        $storeRes = $this->requestAs($this->superAdminToken, 'POST', '/api/v1/roles', [
            'name' => 'operator',
        ]);
        $this->assertStatusOk($storeRes, 201);
        $roleUuid = $storeRes->json('data.role.uuid');

        // 2. Index Roles
        $indexRes = $this->requestAs($this->superAdminToken, 'GET', '/api/v1/roles');
        $this->assertStatusOk($indexRes, 200);

        // 3. Update Role
        $updateRes = $this->requestAs($this->superAdminToken, 'PUT', '/api/v1/roles/' . $roleUuid, [
            'name' => 'operator-senior',
        ]);
        $this->assertStatusOk($updateRes, 200);

        // 4. Delete Role
        $deleteRes = $this->requestAs($this->superAdminToken, 'DELETE', '/api/v1/roles/' . $roleUuid);
        $this->assertStatusOk($deleteRes, 200);
    }

    /**
     * Test Category Management APIs
     */
    public function test_category_management_endpoints(): void
    {
        // 1. Store Category
        $storeRes = $this->requestAs($this->adminToken, 'POST', '/api/v1/item-category', [
            'name' => 'Office Stationeries',
        ]);
        $this->assertStatusOk($storeRes, 201);
        $catUuid = $storeRes->json('data.category.uuid');

        // 2. Index Categories
        $indexRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/item-category');
        $this->assertStatusOk($indexRes, 200);

        // 3. Update Category
        $updateRes = $this->requestAs($this->adminToken, 'PUT', '/api/v1/item-category/' . $catUuid, [
            'name' => 'Office Stationeries (Primary)',
        ]);
        $this->assertStatusOk($updateRes, 200);

        // 4. Delete Category
        $deleteRes = $this->requestAs($this->adminToken, 'DELETE', '/api/v1/item-category/' . $catUuid);
        $this->assertStatusOk($deleteRes, 200);
    }

    /**
     * Test Item Management APIs
     */
    public function test_item_management_endpoints(): void
    {
        // 1. Store Item (Draft)
        $storeRes = $this->requestAs($this->adminToken, 'POST', '/api/v1/items', [
            'category_uuid' => $this->category->uuid,
            'code_item' => 'ITM-ELE-999',
            'name' => 'iPad Air',
            'type' => 'logistic',
            'location' => 'Meeting Room',
            'units' => 10,
        ]);
        $this->assertStatusOk($storeRes, 201);
        $itemUuid = $storeRes->json('data.item.uuid');

        // 2. Index Items
        $indexRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/items');
        $this->assertStatusOk($indexRes, 200);

        // 3. Show Item Detail
        $showRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/items/' . $itemUuid);
        $this->assertStatusOk($showRes, 200);

        // 4. Update Item
        $updateRes = $this->requestAs($this->adminToken, 'PUT', '/api/v1/items/' . $itemUuid, [
            'category_uuid' => $this->category->uuid,
            'name' => 'iPad Air M2',
            'location' => 'Meeting Room A',
        ]);
        $this->assertStatusOk($updateRes, 200);

        // 5. Item Approval Transitions (Draft -> Pending Kasi -> Pending Pust -> Active)
        // Transition to pending_kasi (by Admin)
        $kasiAppRes = $this->requestAs($this->adminToken, 'PATCH', '/api/v1/items/' . $itemUuid . '/status', [
            'status' => 'pending_kasi',
        ]);
        $this->assertStatusOk($kasiAppRes, 200);

        // Transition to pending_pust (by Kasi)
        $pustAppRes = $this->requestAs($this->kasiToken, 'PATCH', '/api/v1/items/' . $itemUuid . '/status', [
            'status' => 'pending_pust',
        ]);
        $this->assertStatusOk($pustAppRes, 200);

        // Transition to active (by Kel Pust)
        $activeAppRes = $this->requestAs($this->kelPustToken, 'PATCH', '/api/v1/items/' . $itemUuid . '/status', [
            'status' => 'active',
        ]);
        $this->assertStatusOk($activeAppRes, 200);

        // 6. Delete Item
        $deleteRes = $this->requestAs($this->adminToken, 'DELETE', '/api/v1/items/' . $itemUuid);
        $this->assertStatusOk($deleteRes, 200);
    }

    /**
     * Test Maintenance, SPK, and Rekap workflows
     */
    public function test_maintenance_spk_and_rekap_endpoints(): void
    {
        // 1. Create Maintenance Request (Draft)
        $storeRes = $this->requestAs($this->adminToken, 'POST', '/api/v1/maintenance', [
            'item_id' => $this->item->uuid,
            'title' => 'Screen flicker repair',
            'priority' => 'high',
            'type' => 'korektif',
            'description' => 'Fix display screen panel',
            'target_completion_expectations' => now()->addDays(5)->toDateString(),
        ]);
        $this->assertStatusOk($storeRes, 201);
        $maintUuid = $storeRes->json('data.uuid');

        // 2. Index Maintenance
        $indexRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/maintenance');
        $this->assertStatusOk($indexRes, 200);

        // 3. Show Maintenance
        $showRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/maintenance/' . $maintUuid);
        $this->assertStatusOk($showRes, 200);

        // 4. Update Maintenance
        $updateRes = $this->requestAs($this->adminToken, 'PUT', '/api/v1/maintenance/' . $maintUuid, [
            'title' => 'Screen flicker repair & panel replacement',
            'priority' => 'high',
            'type' => 'korektif',
            'description' => 'Replace LCD screen completely',
        ]);
        $this->assertStatusOk($updateRes, 200);

        // 5. Status Transition to pending_kasi (by Admin)
        $status1 = $this->requestAs($this->adminToken, 'PATCH', '/api/v1/maintenance/' . $maintUuid . '/status', [
            'status' => 'pending_kasi',
        ]);
        $this->assertStatusOk($status1, 200);

        // 6. Status Transition to pending_pust (by Kasi)
        $status2 = $this->requestAs($this->kasiToken, 'PATCH', '/api/v1/maintenance/' . $maintUuid . '/status', [
            'status' => 'pending_pust',
        ]);
        $this->assertStatusOk($status2, 200);

        // 7. Store SPK (effectively transitions status from pending_pust to in_progress)
        $spkStoreRes = $this->requestAs($this->kelPustToken, 'POST', '/api/v1/spk', [
            'maintenance_uuid' => $maintUuid,
            'tanggal_mulai_efektif' => now()->toDateString(),
            'tanggal_selesai_target' => now()->addDays(5)->toDateString(),
            'pagu_anggaran_disetujui' => 2500000.00,
            'note' => 'Disetujui untuk perbaikan layar LCD.',
        ]);
        $this->assertStatusOk($spkStoreRes, 201);
        $spkUuid = $spkStoreRes->json('data.uuid');

        // 8. Index SPK
        $spkIndexRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/spk');
        $this->assertStatusOk($spkIndexRes, 200);

        // 9. Show SPK
        $spkShowRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/spk/' . $spkUuid);
        $this->assertStatusOk($spkShowRes, 200);

        // 10. Update SPK
        $spkUpdateRes = $this->requestAs($this->kelPustToken, 'PATCH', '/api/v1/spk/' . $spkUuid, [
            'tanggal_mulai_efektif' => now()->toDateString(),
            'tanggal_selesai_target' => now()->addDays(7)->toDateString(),
            'pagu_anggaran_disetujui' => 2800000.00,
        ]);
        $this->assertStatusOk($spkUpdateRes, 200);

        // 11. SPK PDF Export
        $spkPdfRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/spk/' . $spkUuid . '/pdf');
        $this->assertStatusOk($spkPdfRes, 200);

        // 12. Submit Rekap (transitions maintenance status from in_progress to done)
        $rekapRes = $this->requestAs($this->adminToken, 'PATCH', '/api/v1/maintenance/' . $maintUuid . '/rekap', [
            'status' => 'success',
            'ringkasan_tindakan' => 'Layar LCD diganti dengan unit baru dan diuji normal.',
            'realisasi_biaya' => 2750000.00,
        ]);
        $this->assertStatusOk($rekapRes, 200);
        $rekapUuid = $rekapRes->json('data.uuid');

        // 13. Index Rekaps
        $rekapIndexRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/maintenance/rekaps');
        $this->assertStatusOk($rekapIndexRes, 200);

        // 14. Show Rekap
        $rekapShowRes = $this->requestAs($this->adminToken, 'GET', '/api/v1/maintenance/rekaps/' . $rekapUuid);
        $this->assertStatusOk($rekapShowRes, 200);

        // 15. Delete Rekap
        $rekapDeleteRes = $this->requestAs($this->adminToken, 'DELETE', '/api/v1/maintenance/rekaps/' . $rekapUuid);
        $this->assertStatusOk($rekapDeleteRes, 200);

        // 16. Delete SPK (Note: Only possible when maintenance status is draft or revision)
        // Let's force maintenance request status back to revision to allow deletion test
        $maintenanceModel = MaintenanceRequest::where('uuid', $maintUuid)->first();
        $maintenanceModel->update(['status' => 'revision']);

        $spkDeleteRes = $this->requestAs($this->kelPustToken, 'DELETE', '/api/v1/spk/' . $spkUuid);
        $this->assertStatusOk($spkDeleteRes, 200);

        // 17. Delete Maintenance Request
        $maintDeleteRes = $this->requestAs($this->adminToken, 'DELETE', '/api/v1/maintenance/' . $maintUuid);
        $this->assertStatusOk($maintDeleteRes, 200);
    }
}
