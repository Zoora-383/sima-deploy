<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\User;
use App\Services\ItemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ItemStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected ItemService $itemService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemService = $this->app->make(ItemService::class);
    }

    public function test_admin_resubmitting_item_revised_by_kel_pust_goes_directly_to_pending_pust(): void
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
            'status' => 'revision',
        ]);

        // Record a log showing it was revised by Kel Pust
        $item->approvalLogs()->create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => $pustUser->id,
            'status_from' => 'pending_pust',
            'status_to' => 'revision',
            'note' => 'Revised by Kel Pust for test',
        ]);

        // When Admin resubmits to pending_kasi, it should dynamically transition to pending_pust
        $updated = $this->itemService->updateStatus(
            $item->uuid,
            ['status' => 'pending_kasi', 'note' => 'Resubmitting'],
            $adminUser
        );

        $this->assertEquals('pending_pust', $updated->status);
    }

    public function test_admin_resubmitting_item_revised_by_kasi_goes_to_pending_kasi(): void
    {
        $adminRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'admin']);
        $kasiRole = Role::create(['uuid' => Str::uuid()->toString(), 'name' => 'kasi']);

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

        $category = ItemCategory::create(['uuid' => Str::uuid()->toString(), 'name' => 'Electronic']);
        $item = Item::create([
            'uuid' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'user_id' => $adminUser->id,
            'code_item' => 'LOG-ELE-001',
            'name' => 'Laptop Dell',
            'type' => 'logistic',
            'status' => 'revision',
        ]);

        // Record a log showing it was revised by Kasi
        $item->approvalLogs()->create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => $kasiUser->id,
            'status_from' => 'pending_kasi',
            'status_to' => 'revision',
            'note' => 'Revised by Kasi for test',
        ]);

        // When Admin resubmits to pending_kasi, it should transition to pending_kasi
        $updated = $this->itemService->updateStatus(
            $item->uuid,
            ['status' => 'pending_kasi', 'note' => 'Resubmitting'],
            $adminUser
        );

        $this->assertEquals('pending_kasi', $updated->status);
    }
}
