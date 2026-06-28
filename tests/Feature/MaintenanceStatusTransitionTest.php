<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\MaintenanceRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\MaintenanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MaintenanceStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected MaintenanceService $maintenanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->maintenanceService = $this->app->make(MaintenanceService::class);
    }

    public function test_kasi_role_can_transition_from_draft_directly_to_pending_pust(): void
    {
        // 1. Setup roles
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $kasiRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'kasi']);

        // 2. Setup users
        $adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $kasiUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $kasiRole->id,
            'email' => 'kasi@test.com',
            'username' => 'kasi',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        // 3. Setup category and item
        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'active',
        ]);

        // 4. Create maintenance request in draft status
        $maintenance = MaintenanceRequest::create([
            'uuid' => Str::uuid()->toString(),
            'nomor_pengajuan' => 'MNT-2026-0001',
            'item_id' => $item->id,
            'requester_id' => $adminUser->id,
            'title' => 'Fix Keyboard',
            'priority' => 'medium',
            'type' => 'korektif',
            'description' => 'Keyboard keys are not working',
            'target_completion_expectations' => now()->addDays(5)->toDateString(),
            'status' => 'draft',
        ]);

        // 5. Attempt status update to 'pending_kasi' as 'kasi' user
        $updated = $this->maintenanceService->updateStatus(
            $maintenance->uuid,
            ['status' => 'pending_kasi', 'note' => 'Approving draft'],
            $kasiUser
        );

        // 6. Assertions
        $this->assertEquals('pending_pust', $updated->status);
    }

    public function test_kasi_role_can_transition_from_revision_directly_to_pending_pust(): void
    {
        // 1. Setup roles
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $kasiRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'kasi']);

        // 2. Setup users
        $adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $kasiUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $kasiRole->id,
            'email' => 'kasi@test.com',
            'username' => 'kasi',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        // 3. Setup category and item
        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'active',
        ]);

        // 4. Create maintenance request in revision status
        $maintenance = MaintenanceRequest::create([
            'uuid' => Str::uuid()->toString(),
            'nomor_pengajuan' => 'MNT-2026-0002',
            'item_id' => $item->id,
            'requester_id' => $adminUser->id,
            'title' => 'Fix Keyboard',
            'priority' => 'medium',
            'type' => 'korektif',
            'description' => 'Keyboard keys are not working',
            'target_completion_expectations' => now()->addDays(5)->toDateString(),
            'status' => 'revision',
        ]);

        // 5. Attempt status update to 'pending_kasi' as 'kasi' user
        $updated = $this->maintenanceService->updateStatus(
            $maintenance->uuid,
            ['status' => 'pending_kasi', 'note' => 'Resubmitting revised request'],
            $kasiUser
        );

        // 6. Assertions
        $this->assertEquals('pending_pust', $updated->status);
    }

    public function test_cannot_create_maintenance_with_draft_item(): void
    {
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'draft',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Hanya item dengan status active yang dapat diajukan untuk pemeliharaan.");

        $this->maintenanceService->addMaintenance([
            'item_id' => $item->uuid,
            'title' => 'Fix Keyboard',
            'priority' => 'medium',
            'type' => 'korektif',
            'description' => 'Keyboard keys are not working',
            'target_completion_expectations' => now()->addDays(5)->toDateString(),
        ], $adminUser);
    }

    public function test_admin_resubmitting_maintenance_revised_by_kel_pust_goes_directly_to_pending_pust(): void
    {
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $pustRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'kel_pust']);

        $adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $pustUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $pustRole->id,
            'email' => 'pust@test.com',
            'username' => 'pust',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'active',
        ]);

        $maintenance = MaintenanceRequest::create([
            'uuid' => Str::uuid()->toString(),
            'nomor_pengajuan' => 'MNT-2026-0003',
            'item_id' => $item->id,
            'requester_id' => $adminUser->id,
            'title' => 'Fix Keyboard',
            'priority' => 'medium',
            'type' => 'korektif',
            'description' => 'Keyboard keys are not working',
            'target_completion_expectations' => now()->addDays(5)->toDateString(),
            'status' => 'revision',
        ]);

        $maintenance->approvalLogs()->create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => $pustUser->id,
            'status_from' => 'pending_pust',
            'status_to' => 'revision',
            'note' => 'Revised by Kel Pust for test',
        ]);

        $updated = $this->maintenanceService->updateStatus(
            $maintenance->uuid,
            ['status' => 'pending_kasi', 'note' => 'Resubmitting'],
            $adminUser
        );

        $this->assertEquals('pending_pust', $updated->status);
    }

    public function test_validation_fails_with_asset_not_found_when_item_does_not_exist(): void
    {
        $request = new \App\Http\Requests\Maintenance\MaintenanceStoreRequest();
        
        $validator = \Illuminate\Support\Facades\Validator::make([
            'item_id' => (string) Str::uuid(),
            'title' => 'Fix Keyboard',
            'priority' => 'medium',
            'type' => 'korektif',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertEquals('Aset tidak ditemukan.', $validator->errors()->first('item_id'));
    }

    public function test_validation_passes_when_item_exists_regardless_of_status(): void
    {
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $adminUser = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);
        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'draft', // not active
        ]);

        $request = new \App\Http\Requests\Maintenance\MaintenanceStoreRequest();
        
        $validator = \Illuminate\Support\Facades\Validator::make([
            'item_id' => $item->uuid,
            'title' => 'Fix Keyboard',
            'priority' => 'medium',
            'type' => 'korektif',
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }
}

