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

    public function test_kasi_role_can_transition_from_rejected_directly_to_pending_pust(): void
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

        // 4. Create maintenance request in rejected status
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
            'status' => 'rejected',
        ]);

        // 5. Attempt status update to 'pending_kasi' as 'kasi' user
        $updated = $this->maintenanceService->updateStatus(
            $maintenance->uuid,
            ['status' => 'pending_kasi', 'note' => 'Resubmitting rejected request'],
            $kasiUser
        );

        // 6. Assertions
        $this->assertEquals('pending_pust', $updated->status);
    }
}
